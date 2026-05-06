<?php

namespace App\Services;

use App\Models\DatabaseBackupRun;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function databaseConnection(): array
    {
        $connectionName = Config::get('database.default');
        $connection = Config::get("database.connections.{$connectionName}");

        if (! is_array($connection) || ($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Database backup and restore currently supports only MySQL connections.');
        }

        return [
            'connection_name' => (string) $connectionName,
            'host' => (string) ($connection['host'] ?? '127.0.0.1'),
            'port' => (string) ($connection['port'] ?? '3306'),
            'database' => (string) ($connection['database'] ?? ''),
            'username' => (string) ($connection['username'] ?? ''),
            'password' => (string) ($connection['password'] ?? ''),
        ];
    }

    public function createBackup(array $options = []): array
    {
        $connection = $this->databaseConnection();
        $directory = $options['directory'] ?? 'app/backups/manual';
        $prefix = $options['prefix'] ?? $connection['database'] . '-backup';
        $backupType = $options['type'] ?? 'manual';
        $backupDirectory = storage_path($directory);

        File::ensureDirectoryExists($backupDirectory);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFilename = sprintf('%s-%s.sql', $prefix, $timestamp);
        $sqlAbsolutePath = $backupDirectory . DIRECTORY_SEPARATOR . $sqlFilename;

        $run = DatabaseBackupRun::query()->create([
            'backup_automation_setting_id' => $options['setting_id'] ?? null,
            'backup_type' => $backupType,
            'status' => 'running',
            'filename' => $sqlFilename,
            'stored_path' => $this->relativeStoragePath($sqlAbsolutePath),
            'mailed_to' => $options['mailed_to'] ?? null,
            'started_at' => now(),
        ]);

        try {
            try {
                $this->generateBackupUsingMysqldump($connection, $sqlAbsolutePath);
            } catch (RuntimeException $exception) {
                $this->generateBackupUsingDatabaseConnection($connection, $sqlAbsolutePath);
            }

            $finalAbsolutePath = $sqlAbsolutePath;
            $finalFilename = $sqlFilename;
            $mimeType = 'application/sql';

            $run->update([
                'status' => 'success',
                'filename' => $finalFilename,
                'stored_path' => $this->relativeStoragePath($finalAbsolutePath),
                'completed_at' => now(),
            ]);

            return [
                'filename' => $finalFilename,
                'stored_path' => $this->relativeStoragePath($finalAbsolutePath),
                'absolute_path' => $finalAbsolutePath,
                'mime_type' => $mimeType,
            ];
        } catch (\Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            if (File::exists($sqlAbsolutePath)) {
                File::delete($sqlAbsolutePath);
            }

            throw $exception;
        }
    }

    public function restoreFromSqlFile(string $sqlFilePath): void
    {
        $connection = $this->databaseConnection();

        try {
            $this->restoreUsingMysqlClient($connection, $sqlFilePath);
        } catch (RuntimeException $exception) {
            $this->restoreUsingDatabaseConnection($connection, $sqlFilePath);
        }
    }

    private function relativeStoragePath(string $absolutePath): string
    {
        $storageRoot = rtrim(storage_path(), '\\/');

        return ltrim(str_replace($storageRoot, '', $absolutePath), '\\/');
    }

    private function generateBackupUsingMysqldump(array $connection, string $temporaryPath): void
    {
        $dumpBinary = $this->resolveMysqlExecutable('mysqldump');

        $command = [
            $dumpBinary,
            '--host=' . $connection['host'],
            '--port=' . $connection['port'],
            '--user=' . $connection['username'],
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--add-drop-table',
            '--databases',
            $connection['database'],
            '--result-file=' . $temporaryPath,
        ];

        if ($connection['password'] !== '') {
            $command[] = '--password=' . $connection['password'];
        }

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($temporaryPath)) {
            if (File::exists($temporaryPath)) {
                File::delete($temporaryPath);
            }

            throw new RuntimeException($this->formatProcessFailureMessage(
                'Unable to generate the database backup.',
                $process
            ));
        }
    }

    private function generateBackupUsingDatabaseConnection(array $connection, string $temporaryPath): void
    {
        $database = $connection['database'];
        $connectionInstance = DB::connection($connection['connection_name']);
        $pdo = $connectionInstance->getPdo();

        $sql = [];
        $sql[] = '-- DILG-CAR database backup';
        $sql[] = '-- Generated at ' . now()->toDateTimeString();
        $sql[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $sql[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $sql[] = 'SET NAMES utf8mb4;';
        $sql[] = '-- Database: ' . $database;
        $sql[] = '';

        $tables = [];
        $views = [];
        $tableRows = $connectionInstance->select('SHOW FULL TABLES');

        foreach ($tableRows as $tableRow) {
            $row = array_values((array) $tableRow);
            if (count($row) < 2) {
                continue;
            }

            $tableName = (string) $row[0];
            $tableType = strtoupper((string) $row[1]);

            if ($tableType === 'VIEW') {
                $views[] = $tableName;
                continue;
            }

            $tables[] = $tableName;
        }

        foreach ($tables as $tableName) {
            $sql[] = 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($tableName) . ';';

            $createTableRow = (array) $connectionInstance->selectOne('SHOW CREATE TABLE ' . $this->quoteIdentifier($tableName));
            $createTableSql = (string) ($createTableRow['Create Table'] ?? end($createTableRow));
            $sql[] = $createTableSql . ';';
            $sql[] = '';

            $rows = $connectionInstance->table($tableName)->get();
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys((array) $rows->first());
            $columnList = implode(', ', array_map([$this, 'quoteIdentifier'], $columns));

            foreach ($rows->chunk(100) as $chunk) {
                $valueSets = [];

                foreach ($chunk as $row) {
                    $rowArray = (array) $row;
                    $values = [];

                    foreach ($columns as $column) {
                        $values[] = $this->quoteValue($pdo, $rowArray[$column] ?? null);
                    }

                    $valueSets[] = '(' . implode(', ', $values) . ')';
                }

                $sql[] = 'INSERT INTO ' . $this->quoteIdentifier($tableName) . ' (' . $columnList . ') VALUES';
                $sql[] = implode(",\n", $valueSets) . ';';
                $sql[] = '';
            }
        }

        foreach ($views as $viewName) {
            $sql[] = 'DROP VIEW IF EXISTS ' . $this->quoteIdentifier($viewName) . ';';

            $createViewRow = (array) $connectionInstance->selectOne('SHOW CREATE VIEW ' . $this->quoteIdentifier($viewName));
            $createViewSql = (string) ($createViewRow['Create View'] ?? end($createViewRow));
            $sql[] = $createViewSql . ';';
            $sql[] = '';
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $sql[] = '';

        File::put($temporaryPath, implode("\n", $sql));
    }

    private function restoreUsingMysqlClient(array $connection, string $sqlFilePath): void
    {
        $mysqlBinary = $this->resolveMysqlExecutable('mysql');

        $command = [
            $mysqlBinary,
            '--host=' . $connection['host'],
            '--port=' . $connection['port'],
            '--user=' . $connection['username'],
            '--default-character-set=utf8mb4',
            $connection['database'],
        ];

        if ($connection['password'] !== '') {
            $command[] = '--password=' . $connection['password'];
        }

        $stream = fopen($sqlFilePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException('Unable to read the uploaded SQL backup file.');
        }

        DB::disconnect($connection['connection_name']);

        try {
            $process = new Process($command);
            $process->setTimeout(null);
            $process->setInput($stream);
            $process->run();
        } finally {
            fclose($stream);
            DB::purge($connection['connection_name']);
            DB::reconnect($connection['connection_name']);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException($this->formatProcessFailureMessage(
                'Database restore failed.',
                $process
            ));
        }
    }

    private function restoreUsingDatabaseConnection(array $connection, string $sqlFilePath): void
    {
        $handle = fopen($sqlFilePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to read the uploaded SQL backup file.');
        }

        $connectionInstance = DB::connection($connection['connection_name']);
        $statementBuffer = '';
        $delimiter = ';';

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmedLine = trim($line);
                if ($statementBuffer === '' && $this->shouldSkipSqlLine($trimmedLine)) {
                    continue;
                }

                if (preg_match('/^\s*DELIMITER\s+(.+)\s*$/i', $line, $matches) === 1) {
                    $delimiter = trim($matches[1]);
                    continue;
                }

                $statementBuffer .= $line;

                if (! $this->statementEndsWithDelimiter($statementBuffer, $delimiter)) {
                    continue;
                }

                $sql = $this->stripTrailingDelimiter($statementBuffer, $delimiter);
                $statementBuffer = '';

                if (trim($sql) === '') {
                    continue;
                }

                $connectionInstance->unprepared($sql);
            }
        } finally {
            fclose($handle);
            DB::purge($connection['connection_name']);
            DB::reconnect($connection['connection_name']);
        }

        if (trim($statementBuffer) !== '') {
            $connectionInstance->unprepared($statementBuffer);
        }
    }

    private function shouldSkipSqlLine(string $line): bool
    {
        if ($line === '') {
            return true;
        }

        if (str_starts_with($line, '--')) {
            return true;
        }

        if (str_starts_with($line, '#')) {
            return true;
        }

        return str_starts_with($line, '/*')
            && ! str_starts_with($line, '/*!')
            && str_ends_with($line, '*/');
    }

    private function statementEndsWithDelimiter(string $statement, string $delimiter): bool
    {
        $trimmedStatement = rtrim($statement);

        return $delimiter !== ''
            && str_ends_with($trimmedStatement, $delimiter);
    }

    private function stripTrailingDelimiter(string $statement, string $delimiter): string
    {
        $trimmedStatement = rtrim($statement);

        if ($delimiter === '' || ! str_ends_with($trimmedStatement, $delimiter)) {
            return $trimmedStatement;
        }

        return rtrim(substr($trimmedStatement, 0, -strlen($delimiter)));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function quoteValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    private function resolveMysqlExecutable(string $binary): string
    {
        $extension = DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';
        $binaryName = $binary . $extension;
        $finder = new ExecutableFinder();

        $phpRoot = dirname(dirname(PHP_BINARY));
        $customMysqlBinPath = trim((string) env('MYSQL_BIN_PATH', ''));

        $candidates = array_filter([
            $customMysqlBinPath !== '' ? rtrim($customMysqlBinPath, '\\/') . DIRECTORY_SEPARATOR . $binaryName : null,
            $phpRoot . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $binaryName,
            base_path('mysql' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $binaryName),
            $finder->find($binary),
            $finder->find($binaryName),
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && File::exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(sprintf(
            'Unable to locate %s. Set MYSQL_BIN_PATH in your .env file if MySQL is installed in a custom directory.',
            $binaryName
        ));
    }

    private function formatProcessFailureMessage(string $prefix, Process $process): string
    {
        $details = trim($process->getErrorOutput()) ?: trim($process->getOutput());

        return $details === ''
            ? $prefix
            : $prefix . ' ' . $details;
    }
}
