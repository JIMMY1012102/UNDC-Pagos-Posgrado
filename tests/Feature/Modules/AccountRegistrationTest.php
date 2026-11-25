<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use App\Modules\Usuarios\Services\BuyerProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Fixtures\BuyerDocumentCases;
use PHPUnit\Framework\Attributes\DataProviderExternal;

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

    public function test_registration_form_renders_three_steps_and_preserves_old_identity_and_contact_values(): void
    {
        $this->withSession(['_old_input' => [
            'first_names' => 'Ana QA',
            'last_names' => 'Torres Prueba',
            'email' => 'ana@example.test',
            'phone_country_code' => '+51',
            'phone' => '987654321',
            'line_1' => 'Av. de prueba 123',
        ]])->get('/tienda/registro')
            ->assertOk()
            ->assertSee('Paso 1 de 3')
            ->assertSee('Identidad')
            ->assertSee('Contacto y ubicación')
            ->assertSee('Acceso')
            ->assertSee('data-register-step="1"', false)
            ->assertSee('data-register-step="2"', false)
            ->assertSee('data-register-step="3"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('value="Ana QA"', false)
            ->assertSee('value="Torres Prueba"', false)
            ->assertSee('value="ana@example.test"', false)
            ->assertSee('value="987654321"', false)
            ->assertSee('value="Av. de prueba 123"', false);
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
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
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
        $this->get('/tienda/perfil')->assertOk()->assertSee('PERFIL DEL COMPRADOR');
    }

    public function test_profile_form_prefills_trusted_google_names_and_account_email(): void
    {
        $user = User::factory()->create(['email' => 'google@example.test']);
        $this->actingAs($user)->withSession(['market_google_profile' => ['first_names' => 'Persona', 'last_names' => 'Prueba']])
            ->get('/tienda/perfil')->assertOk()->assertSee('value="Persona"', false)->assertSee('google@example.test');
        $this->post('/tienda/perfil', array_merge($this->input(), ['contact_email' => 'attacker@example.test']))
            ->assertRedirect('/tienda/carrito')->assertSessionMissing('market_google_profile');
        $this->assertDatabaseHas('tienda_perfiles_compradores', ['usuario_id' => $user->id, 'correo_contacto' => $user->email]);
    }

    public function test_guest_cannot_store_a_profile(): void
    {
        $this->post('/tienda/perfil', $this->input())->assertRedirect('/tienda/login');
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
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
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
    }


    public function test_cancel_profile_returns_to_login_without_saving_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['market_google_profile' => ['first_names' => 'Demo']])
            ->post('/auth/logout', ['return_to' => 'login'])
            ->assertRedirect('/tienda/login')
            ->assertSessionMissing('market_google_profile');
        $this->assertGuest();
        $this->assertFalse(app(BuyerProfileService::class)->isComplete($user->id));
        $this->get('/tienda/login')->assertOk();
    }

    public function test_logout_ignores_arbitrary_redirect_destinations(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/auth/logout', ['return_to' => 'https://example.test'])
            ->assertRedirect('/tienda');
        $this->assertGuest();
    }

    public function test_logout_without_an_active_session_redirects_to_market_login(): void
    {
        $this->post('/auth/logout')->assertRedirect('/tienda/login');
        $this->assertGuest();
    }

    public function test_guest_api_requests_remain_unauthorized_json(): void
    {
        $this->getJson('/api/v1/acceso/estado')->assertUnauthorized();
    }

    #[DataProviderExternal(BuyerDocumentCases::class, 'invalid')]
    public function test_invalid_document_redirects_registration_without_creating_account(mixed $type, mixed $number, string $field): void
    {
        $this->from('/tienda/registro')->post('/tienda/registro', array_merge($this->input(), [
            'document_type' => $type, 'document_number' => $number,
        ]))->assertRedirect('/tienda/registro')->assertSessionHasErrors($field);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
    }

    #[DataProviderExternal(BuyerDocumentCases::class, 'valid')]
    public function test_registration_normalizes_valid_documents(string $type, string $number, string $expectedType, string $expectedNumber): void
    {
        $this->post('/tienda/registro', array_merge($this->input(), [
            'document_type' => $type, 'document_number' => $number,
        ]))->assertRedirect('/tienda/carrito');
        $this->assertDatabaseHas('tienda_perfiles_compradores', [
            'tipo_documento' => $expectedType, 'numero_documento' => $expectedNumber,
        ]);
    }

    #[DataProviderExternal(BuyerDocumentCases::class, 'invalid')]
    public function test_invalid_document_redirects_web_profile_without_saving(mixed $type, mixed $number, string $field): void
    {
        $this->actingAs(User::factory()->create())->from('/tienda/perfil')
            ->post('/tienda/perfil', array_merge($this->input(), [
                'document_type' => $type, 'document_number' => $number,
            ]))->assertRedirect('/tienda/perfil')->assertSessionHasErrors($field);
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
    }
    public function test_complete_buyer_can_edit_address_and_phone_from_profile_page(): void
    {
        $user = User::factory()->create();
        app(BuyerProfileService::class)->save($user->id, [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
        ]);

        $this->actingAs($user)->get('/tienda/perfil')
            ->assertOk()->assertSee('Dirección')->assertSee('Referencia (opcional)')
            ->assertSee('Ciudad')->assertSee('Departamento / región')
            ->assertSee('País')->assertSee('Código de país')->assertSee('Teléfono');

        $this->post('/tienda/perfil', [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'phone_country_code' => '+51', 'phone' => '987654321',
            'line_1' => 'Av. Ejemplo 123', 'line_2' => 'Dpto. 402',
            'city' => 'Mala', 'state' => 'Lima', 'country' => 'pe',
        ])->assertRedirect('/tienda/carrito');

        $this->assertDatabaseHas('tienda_perfiles_compradores', [
            'usuario_id' => $user->id, 'direccion_linea_1' => 'Av. Ejemplo 123',
            'direccion_linea_2' => 'Dpto. 402', 'ciudad' => 'Mala', 'region' => 'Lima',
            'pais' => 'PE', 'codigo_pais_telefono' => '+51', 'telefono' => '987654321',
        ]);
    }


    public function test_complete_personal_profile_can_open_profile_form_and_guest_is_redirected(): void
    {
        $user = User::factory()->create();
        app(BuyerProfileService::class)->save($user->id, [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
        ]);

        $this->actingAs($user)->get('/tienda/perfil')->assertOk()
            ->assertSee('PERFIL DEL COMPRADOR')
            ->assertSee('value="Persona"', false)
            ->assertSee('value="Prueba"', false);
        auth()->logout();
        $this->get('/tienda/perfil')->assertRedirect('/tienda/login');
    }

    public function test_password_login_with_complete_profile_still_redirects_to_cart(): void
    {
        $user = User::factory()->create([
            'email' => 'complete@example.test', 'password' => 'Demo-password-2026',
        ]);
        app(BuyerProfileService::class)->save($user->id, [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
        ]);

        $this->post('/tienda/login', [
            'email' => $user->email, 'password' => 'Demo-password-2026',
        ])->assertRedirect('/tienda/carrito');
    }

    public function test_invalid_legacy_phone_message_is_used_by_registration_and_profile_form(): void
    {
        $message = 'El teléfono debe tener solo números (7 a 15 dígitos), sin el código de país. El código va en el campo «Código de país».';
        $this->from('/tienda/registro')->post('/tienda/registro', array_merge($this->input(), [
            'phone_country_code' => '+51', 'phone' => '98765abc1',
        ]))->assertRedirect('/tienda/registro')->assertSessionHasErrors(['phone' => $message]);
        $this->assertDatabaseCount('users', 0);

        $user = User::factory()->create();
        $this->actingAs($user)->from('/tienda/perfil')->post('/tienda/perfil', array_merge($this->input(), [
            'phone_country_code' => '+51', 'phone' => '+34600123456',
        ]))->assertRedirect('/tienda/perfil')->assertSessionHasErrors(['phone' => $message]);
    }

}
