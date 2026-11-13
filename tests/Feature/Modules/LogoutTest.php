<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_remains_available_after_exhausting_browsing_rate_limit(): void
    {
        $this->actingAs(User::factory()->create())->withSession([
            '_token' => 'previous-csrf-token',
            'market_google_profile' => ['first_names' => 'Demo'],
        ]);

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->getJson('/api/v1/acceso/estado')->assertOk();
        }
        $this->getJson('/api/v1/acceso/estado')->assertStatus(429);

        $response = $this->post('/auth/logout');
        $response->assertRedirect('/tienda')->assertSessionMissing('market_google_profile');
        $this->assertGuest('web');
        $this->assertNotSame('previous-csrf-token', session()->token());
        $this->getJson('/api/v1/acceso/estado')->assertUnauthorized();
    }

    public function test_logout_can_return_to_login_and_requires_post_and_authentication(): void
    {
        $this->get('/auth/logout')->assertStatus(405);
        $this->post('/auth/logout')->assertRedirect('/tienda/login');
        $this->actingAs(User::factory()->create())
            ->post('/auth/logout', ['return_to' => 'login'])->assertRedirect('/tienda/login');
        $this->assertGuest('web');
    }
}
