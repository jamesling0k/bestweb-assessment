<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndDocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_only_shows_enabled_non_deleted_products(): void
    {
        $visible = Product::factory()->create(['name' => 'Visible lamp']);
        $disabled = Product::factory()->create(['name' => 'Hidden lamp', 'enabled' => false]);
        $deleted = Product::factory()->create(['name' => 'Deleted lamp']);
        $deleted->delete();

        $this->get('/')->assertOk()->assertSee('Visible lamp')->assertDontSee('Hidden lamp')->assertDontSee('Deleted lamp');
        $this->get('/products/'.$visible->id)->assertOk()->assertSee($visible->description);
        $this->get('/products/'.$disabled->id)->assertNotFound();
        $this->get('/products/'.$deleted->id)->assertNotFound();
    }

    public function test_public_catalog_paginates_and_escapes_product_text(): void
    {
        Product::factory()->count(12)->create();
        Product::factory()->create(['name' => '<script>alert(1)</script>']);
        $response = $this->get('/')->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
        $this->assertCount(12, $response->viewData('products')->items());
        $this->get('/?page=2')->assertOk();
    }

    public function test_login_and_documentation_pages_are_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Admin sign in');
        $this->get('/api/documentation')->assertOk()->assertSee('swagger-ui')->assertSee('openapi.json');
        $spec = json_decode(file_get_contents(public_path('openapi.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('3.0.3', $spec['openapi']);
        foreach (['/api/tokens', '/api/tokens/current', '/api/products', '/api/products/{product}', '/api/products/bulk'] as $path) {
            $this->assertArrayHasKey($path, $spec['paths']);
        }
    }
}
