<?php

namespace Tests\Unit;

use App\Exports\ProductsExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductsExportTest extends TestCase
{
    #[DataProvider('textValues')]
    public function test_text_is_never_converted_to_a_formula_or_number(string $value): void
    {
        $workbook = new Spreadsheet;
        $cell = $workbook->getActiveSheet()->getCell('A1');
        (new ProductsExport)->bindValue($cell, $value);
        $this->assertSame($value, $cell->getValue());
        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        $workbook->disconnectWorksheets();
    }

    public static function textValues(): array
    {
        return [['=1+1'], ['+123'], ['-123'], ['@SUM(A1)'], ['00123'], ['Ordinary name']];
    }

    public function test_numeric_values_are_kept_numeric(): void
    {
        $workbook = new Spreadsheet;
        $cell = $workbook->getActiveSheet()->getCell('A1');
        (new ProductsExport)->bindValue($cell, 0);
        $this->assertSame(0, $cell->getValue());
        $this->assertSame(DataType::TYPE_NUMERIC, $cell->getDataType());
        $workbook->disconnectWorksheets();
    }
}
