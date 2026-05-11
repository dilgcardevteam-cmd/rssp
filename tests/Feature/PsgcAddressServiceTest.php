<?php

namespace Tests\Feature;

use App\Services\PsgcAddressService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PsgcAddressServiceTest extends TestCase
{
    public function test_provinces_can_be_loaded_from_dedicated_workbook(): void
    {
        $directory = storage_path('framework/testing/psgc-service');
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $dataFile = $directory . '/psgc-data.xlsx';
        $provinceFile = $directory . '/psgc-provinces.xlsx';
        $cityFile = $directory . '/psgc-cities.xlsx';

        $this->writeWorkbook($dataFile, 'PSGC', [
            ['Code', 'Name', 'Unused', 'Level'],
            ['1300000000', 'National Capital Region', null, 'Reg'],
            ['1380100000', 'Legacy Caloocan Name', null, 'City'],
            ['0100100000', 'Alpha Province', null, 'Prov'],
            ['0100101000', 'Legacy Alpha City Name', null, 'City'],
            ['0100101001', 'Sample Barangay', null, 'Bgy'],
        ]);

        $this->writeWorkbook($provinceFile, 'Provinces', [
            ['PSGC Code', 'Province Name', 'Correspondence Code'],
            ['0200200000', 'Zulu Province', '200200000'],
        ]);

        $this->writeWorkbook($cityFile, 'City-Municipality', [
            ['PSGC Code', 'Name', 'Correspondence Code', 'Geographic Level'],
            ['1380100000', 'City of Caloocan', '137501000', 'City'],
            ['0100101000', 'Alpha City', '100101000', 'Mun'],
            ['1380601000', 'Tondo I/II', '133901000', 'SubMun'],
        ]);

        config()->set('psgc.data_file', $dataFile);
        config()->set('psgc.data_sheet', 'PSGC');
        config()->set('psgc.provinces_file', $provinceFile);
        config()->set('psgc.provinces_sheet', 'Provinces');
        config()->set('psgc.cities_file', $cityFile);
        config()->set('psgc.cities_sheet', 'City-Municipality');

        $service = app(PsgcAddressService::class);

        $this->assertSame([
            ['code' => '0100100000', 'name' => 'Alpha Province'],
            ['code' => '1300000000', 'name' => 'National Capital Region'],
            ['code' => '0200200000', 'name' => 'Zulu Province'],
        ], $service->provinces());

        $this->assertSame([
            ['code' => '0100101000', 'name' => 'Alpha City', 'zip_code' => null],
        ], $service->citiesByParent('0100100000'));

        $this->assertSame([
            ['code' => '1380100000', 'name' => 'City of Caloocan', 'zip_code' => null],
        ], $service->citiesByParent('1300000000'));
    }

    private function writeWorkbook(string $path, string $sheetName, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        foreach ($rows as $rowIndex => $values) {
            foreach ($values as $columnIndex => $value) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValue($columnLetter . ($rowIndex + 1), $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
    }
}
