<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Fixtures\BuyerDocumentCases;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class BuyerProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_or_write_buyer_profile(): void
    {
        $this->getJson('/api/v1/acceso/perfil')->assertUnauthorized();
        $this->putJson('/api/v1/acceso/perfil', [])->assertUnauthorized();
    }

    public function test_authenticated_user_can_save_only_their_profile(): void
    {
        $buyer = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Comprador', 'last_names' => 'Ficticio',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'user_id' => $other->id, 'identity_verified' => true,
        ])->assertOk()->assertJsonPath('data.identity_verified', false);

        $this->assertDatabaseHas('tienda_perfiles_compradores', [
            'usuario_id' => $buyer->id, 'numero_documento' => '00000000',
        ]);
        $this->assertDatabaseMissing('tienda_perfiles_compradores', ['usuario_id' => $other->id]);

        $this->actingAs($other)->getJson('/api/v1/acceso/perfil')
            ->assertOk()->assertJsonPath('data', null);
    }

    public function test_invalid_fields_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->putJson('/api/v1/acceso/perfil', [
            'first_names' => '', 'last_names' => 'X',
            'document_type' => 'DNI', 'document_number' => '',
        ])->assertUnprocessable();
    }

    public function test_profile_update_does_not_duplicate_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        foreach (['Primero', 'Segundo'] as $name) {
            $this->putJson('/api/v1/acceso/perfil', [
                'first_names' => $name, 'last_names' => 'Ejemplo',
                'document_type' => 'DNI', 'document_number' => '00000000',
            ])->assertOk();
        }

        $this->assertDatabaseCount('tienda_perfiles_compradores', 1);
        $this->getJson('/api/v1/acceso/perfil')->assertJsonPath('data.first_names', 'Segundo');
    }

    #[DataProviderExternal(BuyerDocumentCases::class, 'invalid')]
    public function test_invalid_document_preserves_api_error_envelope(mixed $type, mixed $number, string $field): void
    {
        $this->actingAs(User::factory()->create())->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => $type, 'document_number' => $number,
        ])->assertUnprocessable()->assertJsonPath('success', false)->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Datos personales no válidos.')
            ->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('tienda_perfiles_compradores', 0);
    }

    #[DataProviderExternal(BuyerDocumentCases::class, 'valid')]
    public function test_document_is_normalized_before_api_validation_and_storage(string $type, string $number, string $expectedType, string $expectedNumber): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', [
            'first_names' => ' Persona ', 'last_names' => ' Prueba ',
            'document_type' => $type, 'document_number' => $number,
        ])->assertOk()->assertJsonPath('data.document_type', $expectedType)
            ->assertJsonPath('data.document_number', $expectedNumber)->assertJsonPath('data.first_names', 'Persona');
        $this->assertDatabaseHas('tienda_perfiles_compradores', [
            'usuario_id' => $buyer->id, 'tipo_documento' => $expectedType, 'numero_documento' => $expectedNumber,
        ]);
    }
    public function test_address_and_phone_fields_are_saved_normalized_and_returned_by_profile_api(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'phone' => '987654321', 'phone_country_code' => '+51',
            'line_1' => 'Av. Ejemplo 123', 'line_2' => 'Dpto. 402',
            'city' => 'Mala', 'state' => 'Lima', 'country' => 'pe',
        ])->assertOk()
            ->assertJsonPath('data.line_1', 'Av. Ejemplo 123')
            ->assertJsonPath('data.line_2', 'Dpto. 402')
            ->assertJsonPath('data.city', 'Mala')
            ->assertJsonPath('data.state', 'Lima')
            ->assertJsonPath('data.country', 'PE')
            ->assertJsonPath('data.phone_country_code', '+51')
            ->assertJsonPath('data.phone', '987654321');

        $this->getJson('/api/v1/acceso/perfil')->assertOk()
            ->assertJsonPath('data.line_1', 'Av. Ejemplo 123')
            ->assertJsonPath('data.country', 'PE')
            ->assertJsonPath('data.phone_country_code', '+51');
    }

    public function test_invalid_iso_country_and_non_numeric_phone_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'phone' => '98ABC4321', 'country' => 'XX',
        ])->assertUnprocessable()->assertJsonValidationErrors(['phone', 'country']);
    }


    public function test_legacy_phone_formats_are_normalized_and_invalid_numbers_return_spanish_errors(): void
    {
        $buyer = User::factory()->create();
        $base = [
            'first_names' => 'Persona', 'last_names' => 'Prueba',
            'document_type' => 'DNI', 'document_number' => '00000000',
            'phone_country_code' => '+51',
        ];

        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', $base + ['phone' => '987 654 321'])
            ->assertOk()->assertJsonPath('data.phone', '987654321');
        $this->putJson('/api/v1/acceso/perfil', $base + ['phone' => '987-654-321'])
            ->assertOk()->assertJsonPath('data.phone', '987654321');
        $this->putJson('/api/v1/acceso/perfil', $base + ['phone' => '+51987654321'])
            ->assertOk()->assertJsonPath('data.phone', '987654321');

        $message = 'El teléfono debe tener solo números (7 a 15 dígitos), sin el código de país. El código va en el campo «Código de país».';
        $this->putJson('/api/v1/acceso/perfil', $base + ['phone' => '+34600123456'])
            ->assertUnprocessable()->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Datos personales no válidos.')
            ->assertJsonPath('errors.phone.0', $message);
        $this->putJson('/api/v1/acceso/perfil', $base + ['phone' => '98765abc1'])
            ->assertUnprocessable()->assertJsonPath('errors.phone.0', $message);
        $this->putJson('/api/v1/acceso/perfil', array_merge($base, ['phone' => '987654321', 'phone_country_code' => '+0']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.phone_country_code.0', 'El código de país debe tener el formato +51.');
    }
    
}
