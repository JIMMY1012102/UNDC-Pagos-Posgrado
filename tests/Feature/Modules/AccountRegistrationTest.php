<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use App\Modules\Acceso\Services\BuyerProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function input(): array
    {
        return ['first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'email' => 'demo@example.test', 'password' => 'Demo-password-2026',
            'password_confirmation' => 'Demo-password-2026'];
    }

    public function test_registration_creates_account_and_complete_profile_together(): void
    {
        $this->post('/tienda/registro', array_merge($this->input(), ['identity_verified' => true, 'role' => 'admin']))
            ->assertRedirect('/tienda/carrito');
        $user = User::where('email', 'demo@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Demo-password-2026', $user->password));
        $this->assertTrue(app(BuyerProfileService::class)->isComplete($user->id));
        $this->assertSame([], $user->getRoleNames()->all());
        $this->assertFalse(app(BuyerProfileService::class)->find($user->id)['identity_verified']);
    }

    public function test_invalid_profile_or_password_does_not_create_an_account(): void
    {
        $data = $this->input();
        $data['document_number'] = '';
        $data['password_confirmation'] = 'different-password';
        $this->post('/tienda/registro', $data)->assertSessionHasErrors(['document_number', 'password']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('store_customer_profiles', 0);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'demo@example.test']);
        $this->post('/tienda/registro', $this->input())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_wrong_password_does_not_authenticate_or_flash_password(): void
    {
        User::factory()->create(['email' => 'demo@example.test']);
        $this->from('/tienda/login')->post('/tienda/login', [
            'email' => 'demo@example.test', 'password' => 'incorrect-password',
        ])->assertRedirect('/tienda/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_password_login_requires_profile_only_when_incomplete(): void
    {
        $user = User::factory()->create(['email' => 'demo@example.test', 'password' => 'Demo-password-2026']);
        $this->post('/tienda/login', ['email' => $user->email, 'password' => 'Demo-password-2026'])
            ->assertRedirect('/tienda/perfil');
        $this->assertAuthenticatedAs($user);
        $this->post('/tienda/perfil', $this->input())->assertRedirect('/tienda/carrito');
        $this->get('/tienda/perfil')->assertRedirect('/tienda/carrito');
    }

    public function test_profile_form_prefills_trusted_google_names_and_account_email(): void
    {
        $user = User::factory()->create(['email' => 'google@example.test']);
        $this->actingAs($user)->withSession(['market_google_profile' => ['first_names' => 'Persona', 'last_names' => 'Prueba']])
            ->get('/tienda/perfil')->assertOk()->assertSee('value="Persona"', false)->assertSee('google@example.test');
        $this->post('/tienda/perfil', array_merge($this->input(), ['contact_email' => 'attacker@example.test']))
            ->assertRedirect('/tienda/carrito')->assertSessionMissing('market_google_profile');
        $this->assertDatabaseHas('store_customer_profiles', ['user_id' => $user->id, 'contact_email' => $user->email]);
    }

    public function test_guest_cannot_store_a_profile(): void
    {
        $this->post('/tienda/perfil', $this->input())->assertRedirect('/tienda/login');
        $this->assertDatabaseCount('store_customer_profiles', 0);
    }
    public function test_google_callback_opens_profile_before_cart(): void
    {
        config(['services.google.enabled' => true, 'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-only', 'services.google.redirect' => 'https://localhost/auth/google/callback']);
        $google = new \Laravel\Socialite\Two\User();
        $google->id = 'test-subject';
        $google->email = 'oauth@example.test';
        $google->name = 'Persona Prueba';
        $google->user = ['verified_email' => true, 'given_name' => 'Persona', 'family_name' => 'Prueba'];
        $driver = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $driver->shouldReceive('user')->once()->andReturn($google);
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $this->get('https://localhost/auth/google/callback')->assertRedirect('/tienda/perfil')
            ->assertSessionHas('market_google_profile.first_names', 'Persona');
        $this->assertAuthenticated();
        $this->assertDatabaseCount('store_customer_profiles', 0);
    }

}
