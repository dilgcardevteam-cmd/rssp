<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class PsgcAddressService
{
    private const SOURCE_FILE = 'PSGC-4Q-2025-Publication-Datafile.xlsx';
    private const PSGC_SHEET = 'PSGC';
    private static ?array $runtimeData = null;

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
        if (self::$runtimeData !== null) {
            return self::$runtimeData;
        }

        $path = base_path(self::SOURCE_FILE);
        if (!is_file($path)) {
            throw new RuntimeException('PSGC dataset file not found at project root: ' . self::SOURCE_FILE);
        }

        $fingerprint = implode('|', [
            self::SOURCE_FILE,
            (string) @filemtime($path),
            (string) @filesize($path),
        ]);
        $cacheKey = 'psgc.address.data.' . md5($fingerprint);

        try {
            // Use file cache to avoid oversized DB cache payload failures.
            self::$runtimeData = Cache::store('file')->rememberForever($cacheKey, function () use ($path) {
                return $this->buildData($path);
            });
            return self::$runtimeData;
        } catch (\Throwable $e) {
            // Fallback: keep data in-process so PSGC endpoints still work.
            self::$runtimeData = $this->buildData($path);
            return self::$runtimeData;
        }
    }

    private function buildData(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName(self::PSGC_SHEET);

        if ($sheet === null) {
            $spreadsheet->disconnectWorksheets();
            throw new RuntimeException('PSGC sheet not found in ' . self::SOURCE_FILE);
        }

        $provinces = [];
        $citiesByParent = [];
        $barangaysByCity = [];
        $cityIndex = [];

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

        $provinces = $this->sortEntries(array_values($provinces));

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
}
