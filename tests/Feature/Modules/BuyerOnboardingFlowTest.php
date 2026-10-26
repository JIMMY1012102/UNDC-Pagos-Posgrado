<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_sign_in_before_checking_registration(): void
    {
        $this->getJson('/api/v1/acceso/estado')->assertUnauthorized();
    }

    public function test_first_login_requires_profile_completion_only_once(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->getJson('/api/v1/acceso/estado')
            ->assertOk()
            ->assertJsonPath('data.authenticated', true)
            ->assertJsonPath('data.profile_complete', false)
            ->assertJsonPath('data.next_step', 'complete_profile');

        $payload = [
            'first_names' => 'Persona',
            'last_names' => 'Prueba',
            'document_type' => 'DNI',
            'document_number' => '00000000',
            'phone' => '900000000',
        ];
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', $payload)->assertOk();
        $this->actingAs($buyer)->getJson('/api/v1/acceso/estado')
            ->assertOk()
            ->assertJsonPath('data.profile_complete', true)
            ->assertJsonPath('data.next_step', 'tienda')
            ->assertJsonPath('data.profile.document_number', '00000000')
            ->assertJsonPath('data.profile.identity_verified', false);
        $this->assertDatabaseCount('store_customer_profiles', 1);

        // New session for same account: no second registration is required.
        $this->flushSession();
        $this->actingAs($buyer->fresh())->getJson('/api/v1/acceso/estado')
            ->assertJsonPath('data.profile_complete', true)
            ->assertJsonPath('data.next_step', 'tienda');
        $this->assertDatabaseCount('store_customer_profiles', 1);
    }

    public function test_incomplete_legacy_profile_still_requires_registration(): void
    {
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Nombre', 'last_names' => 'Apellido',
            'document_type' => 'DNI', 'document_number' => '00000001',
        ])->assertOk();

        \Illuminate\Support\Facades\DB::table('store_customer_profiles')
            ->where('user_id', $buyer->id)->update(['document_number' => null]);

        $this->actingAs($buyer)->getJson('/api/v1/acceso/estado')
            ->assertJsonPath('data.profile_complete', false)
            ->assertJsonPath('data.next_step', 'complete_profile');
    }

    public function test_buyer_cannot_check_another_account_profile(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->putJson('/api/v1/acceso/perfil', [
            'first_names' => 'Primera', 'last_names' => 'Cuenta',
            'document_type' => 'DNI', 'document_number' => '00000002',
        ])->assertOk();

        $this->actingAs($second)->getJson('/api/v1/acceso/estado')
            ->assertJsonPath('data.profile_complete', false)
            ->assertJsonPath('data.profile', null);
    }
}
