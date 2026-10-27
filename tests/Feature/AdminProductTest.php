<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_admin_can_open_forms_and_create_and_update_a_product(): void
    {
        $category = Category::factory()->create();
        $this->get('/admin/products/create')->assertOk()->assertSee($category->name);
        $data = [
            'name' => 'Desk lamp', 'category_id' => $category->id,
            'description' => 'Adjustable light.', 'price' => '49.90', 'stock' => 12, 'enabled' => 1,
        ];
        $this->post('/admin/products', $data)->assertRedirect('/admin/products');
        $product = Product::firstOrFail();
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk()->assertSee('Desk lamp');
        $this->put('/admin/products/'.$product->id, [...$data, 'name' => 'Updated lamp', 'enabled' => 0])
            ->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated lamp', 'enabled' => 0]);
    }

    public function test_invalid_update_does_not_change_the_product(): void
    {
        $product = Product::factory()->create(['name' => 'Original name']);
        $data = $product->only(['name', 'category_id', 'description', 'price', 'stock', 'enabled']);
        $this->from('/admin/products/'.$product->id.'/edit')
            ->put('/admin/products/'.$product->id, [...$data, 'name' => 'Changed', 'stock' => -1])
            ->assertSessionHasErrors('stock');
        $this->assertSame('Original name', $product->fresh()->name);
    }

    public function test_admin_list_filters_disabled_products_and_paginates(): void
    {
        Product::factory()->count(3)->create(['enabled' => false]);
        Product::factory()->create(['enabled' => true]);
        $response = $this->get('/admin/products?enabled=0&per_page=2')->assertOk();
        $products = $response->viewData('products');
        $this->assertSame(3, $products->total());
        $this->assertCount(2, $products->items());
        $this->assertFalse($products->first()->enabled);
        $response->assertSee('enabled=0', false);
    }

    public function test_admin_can_soft_delete_and_bulk_delete(): void
    {
        $products = Product::factory()->count(4)->create();
        $this->delete('/admin/products/'.$products[0]->id)->assertRedirect('/admin/products');
        $this->delete('/admin/products/bulk', ['ids' => [$products[1]->id, $products[2]->id]])
            ->assertRedirect('/admin/products');
        foreach ($products->take(3) as $product) {
            $this->assertSoftDeleted($product);
        }
        $this->assertNotSoftDeleted($products[3]);
        $this->get('/admin/products/'.$products[0]->id.'/edit')->assertNotFound();
    }

    public function test_export_download_is_a_readable_xlsx_with_filters_and_safe_text(): void
    {
        $category = Category::factory()->create(['name' => 'Office']);
        Product::factory()->create([
            'category_id' => $category->id, 'name' => '=1+1', 'price' => 0,
            'stock' => 0, 'description' => 'Test export.', 'enabled' => false,
        ]);
        Product::factory()->create(['category_id' => $category->id, 'enabled' => true]);
        Product::factory()->create(['enabled' => false]);
        $deleted = Product::factory()->create(['category_id' => $category->id, 'enabled' => false]);
        $deleted->delete();

        $response = $this->get('/admin/products/export?enabled=0&category_id='.$category->id)
            ->assertOk()->assertDownload('products.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();

        try {
            $this->assertSame('PK', file_get_contents($path, false, null, 0, 2));
            $workbook = IOFactory::load($path);
            $sheet = $workbook->getActiveSheet();
            $this->assertSame(2, $sheet->getHighestDataRow());
            $this->assertSame('Name', $sheet->getCell('B1')->getValue());
            $this->assertSame('=1+1', $sheet->getCell('B2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
            $this->assertSame('Office', $sheet->getCell('D2')->getValue());
            $this->assertEquals(0, $sheet->getCell('F2')->getValue());
            $this->assertNotNull($sheet->getCell('F2')->getValue());
            $this->assertNotNull($sheet->getCell('G2')->getValue());
            $this->assertSame('No', $sheet->getCell('H2')->getValue());
            $workbook->disconnectWorksheets();
        } finally {
            @unlink($path);
        }
    }

    public function test_export_with_no_products_still_contains_headings(): void
    {
        $response = $this->get('/admin/products/export')->assertOk()->assertDownload('products.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();

        try {
            $workbook = IOFactory::load($path);
            $this->assertSame(1, $workbook->getActiveSheet()->getHighestDataRow());
            $this->assertSame('ID', $workbook->getActiveSheet()->getCell('A1')->getValue());
            $workbook->disconnectWorksheets();
        } finally {
            @unlink($path);
        }
    }
}
