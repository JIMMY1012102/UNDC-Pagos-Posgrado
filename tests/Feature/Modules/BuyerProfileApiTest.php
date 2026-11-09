<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'user_id' => $buyer->id, 'document_number' => '00000000',
        ]);
        $this->assertDatabaseMissing('tienda_perfiles_compradores', ['user_id' => $other->id]);

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
}
