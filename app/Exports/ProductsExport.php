<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ProductsExport extends DefaultValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping, WithStrictNullComparison
{
    public function __construct(private array $filters = []) {}

    public function query(): Builder
    {
        return Product::with('category')->filter($this->filters)->orderBy('id');
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Category ID', 'Category', 'Description', 'Price', 'Stock', 'Enabled'];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category_id,
            $product->category->name,
            $product->description,
            (float) $product->price,
            $product->stock,
            $product->enabled ? 'Yes' : 'No',
        ];
    }

    public function bindValue(Cell $cell, $value = null): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
