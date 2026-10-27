<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    private function payload(): array
    {
        return [
            'name' => 'Desk lamp',
            'category_id' => Category::factory()->create()->id,
            'description' => 'Adjustable desk lamp.',
            'price' => '49.90',
            'stock' => 12,
            'enabled' => true,
        ];
    }

    public function test_create_and_detail_include_category_id_and_name(): void
    {
        $data = $this->payload();
        $category = Category::findOrFail($data['category_id']);
        $response = $this->postJson('/api/products', $data)
            ->assertCreated()
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.category_name', $category->name)
            ->assertJsonPath('data.price', '49.90')
            ->assertJsonPath('data.enabled', true);

        $this->assertDatabaseHas('products', ['id' => $response->json('data.id'), 'name' => 'Desk lamp']);
        $this->getJson('/api/products/'.$response->json('data.id'))
            ->assertOk()->assertJsonPath('data.category_name', $category->name);
    }

    #[DataProvider('invalidProducts')]
    public function test_product_validation_rejects_invalid_values(string $field, mixed $value): void
    {
        $data = $this->payload();
        $data[$field] = $value;
        $this->postJson('/api/products', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('products', 0);
    }

    public static function invalidProducts(): array
    {
        return [
            'empty name' => ['name', ''],
            'long name' => ['name', str_repeat('a', 256)],
            'invalid category' => ['category_id', 999999],
            'missing description' => ['description', null],
            'negative price' => ['price', -1],
            'too many decimal places' => ['price', '1.234'],
            'price outside database range' => ['price', '10000000000.00'],
            'negative stock' => ['stock', -1],
            'fractional stock' => ['stock', 1.5],
            'stock outside database range' => ['stock', 4294967296],
            'invalid status' => ['enabled', 'yes'],
        ];
    }

    public function test_zero_price_and_stock_are_valid_and_extra_fields_are_ignored(): void
    {
        $this->postJson('/api/products', [...$this->payload(),
            'price' => 0, 'stock' => 0, 'enabled' => false, 'deleted_at' => now()->toISOString(),
        ])->assertCreated()->assertJsonPath('data.price', '0.00')->assertJsonPath('data.stock', 0);
        $this->assertNull(Product::firstOrFail()->deleted_at);
    }

    public function test_list_filters_by_category_and_status_and_paginates(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'enabled' => true]);
        Product::factory()->create(['category_id' => $category->id, 'enabled' => false]);
        Product::factory()->create();
        $deleted = Product::factory()->create(['category_id' => $category->id]);
        $deleted->delete();

        $url = '/api/products?category_id='.$category->id.'&enabled=1&per_page=2';
        $response = $this->getJson($url)->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)->assertJsonPath('meta.last_page', 2);
        foreach ($response->json('data') as $product) {
            $this->assertSame($category->id, $product['category_id']);
            $this->assertTrue($product['enabled']);
        }
        $this->getJson($url.'&page=2')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/products?enabled=0')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->getJson('/api/products?category_id=999999&enabled=yes&per_page=101&page=0')
            ->assertUnprocessable()->assertJsonValidationErrors(['category_id', 'enabled', 'per_page', 'page']);
    }

    public function test_delete_is_soft_and_deleted_products_are_not_returned(): void
    {
        $product = Product::factory()->create();
        $this->deleteJson('/api/products/'.$product->id)->assertOk();
        $this->assertSoftDeleted($product);
        $this->getJson('/api/products/'.$product->id)->assertNotFound();
        $this->deleteJson('/api/products/'.$product->id)->assertNotFound();
        $this->getJson('/api/products')->assertJsonCount(0, 'data');
    }

    public function test_bulk_delete_removes_only_selected_products(): void
    {
        $products = Product::factory()->count(3)->create();
        $ids = $products->take(2)->pluck('id')->all();
        $this->deleteJson('/api/products/bulk', ['ids' => $ids])
            ->assertOk()->assertJsonPath('data.deleted_count', 2);
        $this->assertSoftDeleted($products[0]);
        $this->assertSoftDeleted($products[1]);
        $this->assertNotSoftDeleted($products[2]);
    }

    public function test_invalid_bulk_request_does_not_partially_delete_products(): void
    {
        $product = Product::factory()->create();
        $this->deleteJson('/api/products/bulk', ['ids' => [$product->id, 999999]])
            ->assertUnprocessable()->assertJsonValidationErrors('ids.1');
        $this->assertNotSoftDeleted($product);
        $this->deleteJson('/api/products/bulk', ['ids' => [$product->id, $product->id]])->assertUnprocessable();
        $this->deleteJson('/api/products/bulk', ['ids' => []])->assertUnprocessable();
        $this->deleteJson('/api/products/bulk', ['ids' => range(1, 101)])->assertUnprocessable();
        $product->delete();
        $this->deleteJson('/api/products/bulk', ['ids' => [$product->id]])->assertUnprocessable();
    }

    public function test_unknown_product_returns_json_404(): void
    {
        $this->getJson('/api/products/999999')->assertNotFound()->assertJsonStructure(['message']);
    }
}
