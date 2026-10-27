<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_pages_or_product_api(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
        $this->getJson('/api/products')->assertUnauthorized();
        $this->postJson('/api/products', [])->assertUnauthorized();
        $this->deleteJson('/api/products/1')->assertUnauthorized();
        $this->deleteJson('/api/products/bulk', ['ids' => [1]])->assertUnauthorized();
        $this->get('/admin/products/export')->assertRedirect('/login');
    }

    public function test_authenticated_non_admin_cannot_use_management_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/products')->assertForbidden();
        $this->post('/admin/products', [])->assertForbidden();
        $this->get('/admin/products/export')->assertForbidden();
        $this->delete('/admin/products/bulk', ['ids' => [1]])->assertForbidden();

        $token = $user->createToken('test')->plainTextToken;
        $this->withToken($token)->getJson('/api/products')->assertForbidden();
        $this->withToken($token)->postJson('/api/products', [])->assertForbidden();
        $this->withToken($token)->deleteJson('/api/products/bulk', ['ids' => [1]])->assertForbidden();
    }

    public function test_seeded_admin_can_sign_in_and_sign_out(): void
    {
        config(['assessment.admin' => [
            'name' => 'Assessment Admin',
            'email' => 'admin@example.test',
            'password' => 'local-test-password',
        ]]);
        $this->seed();
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('local-test-password', $admin->password));

        $this->post('/login', ['email' => $admin->email, 'password' => 'local-test-password'])
            ->assertRedirect('/admin/products');
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_sign_into_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'test-password'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_obtain_and_revoke_a_real_api_token(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->postJson('/api/tokens', ['email' => $admin->email, 'password' => 'test-password'])
            ->assertCreated()->assertJsonPath('data.token_type', 'Bearer');
        $token = $response->json('data.token');
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNotSame($token, $admin->tokens()->first()->token);

        $this->withToken($token)->getJson('/api/products')->assertOk();
        $this->withToken($token)->deleteJson('/api/tokens/current')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/products')->assertUnauthorized();
    }

    public function test_invalid_credentials_and_non_admin_token_requests_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/tokens', ['email' => $user->email, 'password' => 'wrong'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/api/tokens', ['email' => $user->email, 'password' => 'test-password'])
            ->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/tokens', ['email' => 'missing@example.test', 'password' => 'wrong'])
                ->assertUnprocessable();
        }

        $this->postJson('/api/tokens', ['email' => 'missing@example.test', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_expired_tokens_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test');
        $token->accessToken->forceFill(['created_at' => now()->subHours(25)])->save();
        $this->withToken($token->plainTextToken)->getJson('/api/products')->assertUnauthorized();
    }

    public function test_a_downgraded_admin_loses_api_access(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test')->plainTextToken;
        $admin->forceFill(['is_admin' => false])->save();
        $this->withToken($token)->getJson('/api/products')->assertForbidden();
    }
}
