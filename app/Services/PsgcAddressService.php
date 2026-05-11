<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class PsgcAddressService
{
    private const DEFAULT_SOURCE_FILE = 'PSGC-4Q-2025-Publication-Datafile.xlsx';
    private const DEFAULT_PSGC_SHEET = 'PSGC';
    private const DEFAULT_PROVINCES_SOURCE_FILE = 'PSGC_Provinces.xlsx';
    private const DEFAULT_PROVINCES_SHEET = 'Provinces';
    private const DEFAULT_CITIES_SOURCE_FILE = 'PSGC_City_Municipality.xlsx';
    private const DEFAULT_CITIES_SHEET = 'City-Municipality';
    private static array $runtimeData = [];

    public function provinces(): array
    {
        return $this->loadData()['provinces'];
    }

    public function citiesByParent(string $parentCode): array
    {
        $code = $this->normalizeCode($parentCode);
        return $this->loadData()['cities_by_parent'][$code] ?? [];
    }

    public function barangaysByCity(string $cityCode): array
    {
        $code = $this->normalizeCode($cityCode);
        return $this->loadData()['barangays_by_city'][$code] ?? [];
    }

    public function city(string $cityCode): ?array
    {
        $code = $this->normalizeCode($cityCode);
        return $this->loadData()['city_index'][$code] ?? null;
    }

    private function loadData(): array
    {
        $psgcConfig = config('psgc', []);
        $path = $this->resolveConfiguredPath((string) ($psgcConfig['data_file'] ?? self::DEFAULT_SOURCE_FILE));
        $provincePath = $this->resolveOptionalConfiguredPath((string) ($psgcConfig['provinces_file'] ?? self::DEFAULT_PROVINCES_SOURCE_FILE));
        $cityPath = $this->resolveOptionalConfiguredPath((string) ($psgcConfig['cities_file'] ?? self::DEFAULT_CITIES_SOURCE_FILE));

        if (!is_file($path)) {
            throw new RuntimeException('PSGC dataset file not found: ' . $path);
        }

        $fingerprintParts = [
            (string) @filemtime($path),
            (string) @filesize($path),
            $path,
        ];

        if ($provincePath !== null && is_file($provincePath)) {
            $fingerprintParts[] = (string) @filemtime($provincePath);
            $fingerprintParts[] = (string) @filesize($provincePath);
            $fingerprintParts[] = $provincePath;
        }

        if ($cityPath !== null && is_file($cityPath)) {
            $fingerprintParts[] = (string) @filemtime($cityPath);
            $fingerprintParts[] = (string) @filesize($cityPath);
            $fingerprintParts[] = $cityPath;
        }

        $fingerprintParts[] = md5(json_encode([
            'data_sheet' => $psgcConfig['data_sheet'] ?? self::DEFAULT_PSGC_SHEET,
            'provinces_sheet' => $psgcConfig['provinces_sheet'] ?? self::DEFAULT_PROVINCES_SHEET,
            'cities_sheet' => $psgcConfig['cities_sheet'] ?? self::DEFAULT_CITIES_SHEET,
            'city_parent_overrides' => $psgcConfig['city_parent_overrides'] ?? [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

        $fingerprint = implode('|', $fingerprintParts);
        $cacheKey = 'psgc.address.data.' . md5($fingerprint);

        if (array_key_exists($cacheKey, self::$runtimeData)) {
            return self::$runtimeData[$cacheKey];
        }

        try {
            // Use file cache to avoid oversized DB cache payload failures.
            self::$runtimeData[$cacheKey] = Cache::store('file')->rememberForever($cacheKey, function () use ($path, $provincePath, $cityPath) {
                return $this->buildData($path, $provincePath, $cityPath);
            });
            return self::$runtimeData[$cacheKey];
        } catch (\Throwable $e) {
            // Fallback: keep data in-process so PSGC endpoints still work.
            self::$runtimeData[$cacheKey] = $this->buildData($path, $provincePath, $cityPath);
            return self::$runtimeData[$cacheKey];
        }
    }

    private function buildData(string $path, ?string $provincePath = null, ?string $cityPath = null): array
    {
        $spreadsheet = $this->loadSpreadsheet($path);
        $sheet = $spreadsheet->getSheetByName((string) config('psgc.data_sheet', self::DEFAULT_PSGC_SHEET));

        if ($sheet === null) {
            $spreadsheet->disconnectWorksheets();
            throw new RuntimeException('PSGC sheet not found in ' . $path);
        }

        $provinces = [];
        $citiesByParent = [];
        $barangaysByCity = [];
        $cityIndex = [];
        $parentByCity = [];

        $currentRegion = null;
        $currentProvince = null;
        $currentCityCode = null;
        $currentSubMunCityCode = null;

        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $code = $this->normalizeCode($sheet->getCell("A{$row}")->getFormattedValue());
            $name = trim((string) $sheet->getCell("B{$row}")->getFormattedValue());
            $level = trim((string) $sheet->getCell("D{$row}")->getFormattedValue());

            if ($code === '' || $name === '') {
                continue;
            }

            if ($level === 'Reg') {
                $currentRegion = ['code' => $code, 'name' => $name];
                $currentProvince = null;
                $currentCityCode = null;
                $currentSubMunCityCode = null;
                continue;
            }

            if ($level === 'Prov' || ($level === '' && str_ends_with($code, '0000'))) {
                $currentProvince = ['code' => $code, 'name' => $name];
                $currentCityCode = null;
                $currentSubMunCityCode = null;
                $provinces[$code] = ['code' => $code, 'name' => $name];
                continue;
            }

            if ($level === 'City' || $level === 'Mun') {
                $parentCode = $currentProvince['code'] ?? ($currentRegion['code'] ?? null);
                $parentName = $currentProvince['name'] ?? ($currentRegion['name'] ?? null);

                if ($parentCode === null || $parentName === null) {
                    continue;
                }

                if (!isset($provinces[$parentCode])) {
                    // Regions with direct city/municipality children (e.g., NCR) are exposed as "province" options.
                    $provinces[$parentCode] = ['code' => $parentCode, 'name' => $parentName];
                }

                if (!isset($citiesByParent[$parentCode])) {
                    $citiesByParent[$parentCode] = [];
                }

                $cityPayload = [
                    'code' => $code,
                    'name' => $name,
                    'zip_code' => null,
                ];

                $citiesByParent[$parentCode][$code] = $cityPayload;
                $cityIndex[$code] = $cityPayload;
                $parentByCity[$code] = $parentCode;

                if (!isset($barangaysByCity[$code])) {
                    $barangaysByCity[$code] = [];
                }

                $currentCityCode = $code;
                $currentSubMunCityCode = null;
                continue;
            }

            if ($level === 'SubMun') {
                // Barangays under sub-municipality rows belong to the current city.
                $currentSubMunCityCode = $currentCityCode;
                continue;
            }

            if ($level === 'Bgy') {
                $targetCityCode = $currentSubMunCityCode ?: $currentCityCode;
                if ($targetCityCode === null) {
                    continue;
                }

                if (!isset($barangaysByCity[$targetCityCode])) {
                    $barangaysByCity[$targetCityCode] = [];
                }

                $barangaysByCity[$targetCityCode][$code] = [
                    'code' => $code,
                    'name' => $name,
                ];
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $provinces = $this->mergeProvinceOverrides($provinces, $provincePath);
        [$citiesByParent, $cityIndex] = $this->mergeCityOverrides($citiesByParent, $cityIndex, $parentByCity, $cityPath);

        foreach ($citiesByParent as $parentCode => $items) {
            $citiesByParent[$parentCode] = $this->sortEntries(array_values($items));
        }
        ksort($citiesByParent);

        foreach ($barangaysByCity as $cityCode => $items) {
            $barangaysByCity[$cityCode] = $this->sortEntries(array_values($items));
        }
        ksort($barangaysByCity);

        ksort($cityIndex);

        return [
            'provinces' => $provinces,
            'cities_by_parent' => $citiesByParent,
            'barangays_by_city' => $barangaysByCity,
            'city_index' => $cityIndex,
        ];
    }

    private function mergeProvinceOverrides(array $defaultProvinces, ?string $provincePath): array
    {
        $defaultProvinces = $this->indexEntriesByCode($defaultProvinces);
        $provinceOverrides = $this->loadProvinceOverrides($provincePath);

        if ($provinceOverrides !== []) {
            $defaultProvinces = array_replace($defaultProvinces, $this->indexEntriesByCode($provinceOverrides));
        }

        return $this->sortEntries(array_values($defaultProvinces));
    }

    private function loadProvinceOverrides(?string $provincePath): array
    {
        if ($provincePath === null || !is_file($provincePath)) {
            return [];
        }

        $spreadsheet = $this->loadSpreadsheet($provincePath);
        $sheet = $spreadsheet->getSheetByName((string) config('psgc.provinces_sheet', self::DEFAULT_PROVINCES_SHEET))
            ?? $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $headers = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, false)[0] ?? [];
        $headerMap = [];

        foreach ($headers as $index => $header) {
            $normalizedHeader = strtolower(trim((string) $header));
            if ($normalizedHeader !== '') {
                $headerMap[$normalizedHeader] = $index;
            }
        }

        $codeIndex = $headerMap['psgc code'] ?? 0;
        $nameIndex = $headerMap['province name'] ?? 1;

        $provinces = [];
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $values = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, false)[0] ?? [];
            $code = $this->normalizeCode($values[$codeIndex] ?? '');
            $name = trim((string) ($values[$nameIndex] ?? ''));

            if ($code === '' || $name === '') {
                continue;
            }

            $provinces[$code] = [
                'code' => $code,
                'name' => $name,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->sortEntries(array_values($provinces));
    }

    private function mergeCityOverrides(array $defaultCitiesByParent, array $defaultCityIndex, array $parentByCity, ?string $cityPath): array
    {
        $cityOverrides = $this->loadCityOverrides($cityPath);
        if ($cityOverrides === []) {
            return [$defaultCitiesByParent, $defaultCityIndex];
        }

        $configuredParentOverrides = $this->normalizedParentOverrideMap();

        $citiesByParent = [];
        $cityIndex = [];

        foreach ($cityOverrides as $cityCode => $cityPayload) {
            $parentCode = $configuredParentOverrides[$cityCode] ?? ($parentByCity[$cityCode] ?? null);
            if ($parentCode === null) {
                continue;
            }

            if (!isset($citiesByParent[$parentCode])) {
                $citiesByParent[$parentCode] = [];
            }

            $citiesByParent[$parentCode][$cityCode] = $cityPayload;
            $cityIndex[$cityCode] = $cityPayload;
        }

        if ($citiesByParent === []) {
            return [$defaultCitiesByParent, $defaultCityIndex];
        }

        return [$citiesByParent, $cityIndex];
    }

    private function loadCityOverrides(?string $cityPath): array
    {
        if ($cityPath === null || !is_file($cityPath)) {
            return [];
        }

        $spreadsheet = $this->loadSpreadsheet($cityPath);
        $sheet = $spreadsheet->getSheetByName((string) config('psgc.cities_sheet', self::DEFAULT_CITIES_SHEET))
            ?? $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $headers = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, false)[0] ?? [];
        $headerMap = $this->buildHeaderMap($headers);

        $codeIndex = $headerMap['psgc code'] ?? 0;
        $nameIndex = $headerMap['name'] ?? 1;
        $levelIndex = $headerMap['geographic level'] ?? 3;

        $cities = [];
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $values = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, false)[0] ?? [];
            $code = $this->normalizeCode($values[$codeIndex] ?? '');
            $name = trim((string) ($values[$nameIndex] ?? ''));
            $level = trim((string) ($values[$levelIndex] ?? ''));

            if ($code === '' || $name === '' || !in_array($level, ['City', 'Mun'], true)) {
                continue;
            }

            $cities[$code] = [
                'code' => $code,
                'name' => $name,
                'zip_code' => null,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $cities;
    }

    private function loadSpreadsheet(string $path): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        return $reader->load($path);
    }

    private function buildHeaderMap(array $headers): array
    {
        $headerMap = [];

        foreach ($headers as $index => $header) {
            $normalizedHeader = strtolower(trim((string) $header));
            if ($normalizedHeader !== '') {
                $headerMap[$normalizedHeader] = $index;
            }
        }

        return $headerMap;
    }

    private function indexEntriesByCode(array $entries): array
    {
        $indexed = [];

        foreach ($entries as $entry) {
            $code = (string) ($entry['code'] ?? '');
            if ($code === '') {
                continue;
            }

            $indexed[$code] = $entry;
        }

        return $indexed;
    }

    private function normalizedParentOverrideMap(): array
    {
        $normalized = [];
        $overrides = config('psgc.city_parent_overrides', []);

        if (!is_array($overrides)) {
            return $normalized;
        }

        foreach ($overrides as $cityCode => $parentCode) {
            $normalizedCityCode = $this->normalizeCode((string) $cityCode);
            $normalizedParentCode = $this->normalizeCode((string) $parentCode);

            if ($normalizedCityCode === '' || $normalizedParentCode === '') {
                continue;
            }

            $normalized[$normalizedCityCode] = $normalizedParentCode;
        }

        return $normalized;
    }

    private function sortEntries(array $entries): array
    {
        usort($entries, function (array $a, array $b): int {
            $nameCmp = strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
            if ($nameCmp !== 0) {
                return $nameCmp;
            }

            return strcmp((string) ($a['code'] ?? ''), (string) ($b['code'] ?? ''));
        });

        return $entries;
    }

    private function normalizeCode($raw): string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);
        if ($digits === null || $digits === '') {
            return '';
        }

        if (strlen($digits) < 10) {
            $digits = str_pad($digits, 10, '0', STR_PAD_LEFT);
        }

        return substr($digits, 0, 10);
    }

    private function resolveConfiguredPath(string $path): string
    {
        if ($path === '') {
            return base_path(self::DEFAULT_SOURCE_FILE);
        }

        return $this->isAbsolutePath($path) ? $path : base_path($path);
    }

    private function resolveOptionalConfiguredPath(string $path): ?string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        return $this->resolveConfiguredPath($trimmed);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $path) === 1;
    }
}
