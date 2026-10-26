<?php

namespace Tests\Feature\Acceso;

use App\Models\User;
use Database\Seeders\StoreRolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class StoreRolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_registers_shop_roles_without_assigning_privileges_to_users(): void
    {
        $this->seed(StoreRolesPermissionsSeeder::class);

        $user = User::factory()->create();

        $this->assertSame(4, Role::count());
        $this->assertTrue($user->roles->isEmpty());
        $this->assertFalse($user->can('roles.gestionar'));
        $this->assertFalse($user->can('pagos.ver-tesoreria'));
    }

    public function test_buyer_cannot_manage_catalog_and_admin_cannot_validate_treasury_payments(): void
    {
        $this->seed(StoreRolesPermissionsSeeder::class);

        $buyer = User::factory()->create();
        $buyer->assignRole('comprador');

        $admin = User::factory()->create();
        $admin->assignRole('administrador-oficina');

        $this->assertTrue($buyer->can('pedidos.crear'));
        $this->assertFalse($buyer->can('catalogo.gestionar'));
        $this->assertTrue($admin->can('catalogo.gestionar'));
        $this->assertFalse($admin->can('pagos.ver-tesoreria'));
    }

    public function test_protected_route_requires_the_right_permission(): void
    {
        $this->seed(StoreRolesPermissionsSeeder::class);

        // Solo ruta de prueba; no se publica ningún endpoint nuevo en el sistema.
        Route::middleware(['web', 'auth', 'permission:catalogo.gestionar'])
            ->get('/__test/catalogo-gestion', fn () => response()->json(['ok' => true]));

        $buyer = User::factory()->create();
        $buyer->assignRole('comprador');

        $admin = User::factory()->create();
        $admin->assignRole('administrador-oficina');

        $this->actingAs($buyer)->getJson('/__test/catalogo-gestion')->assertForbidden();
        $this->actingAs($admin)->getJson('/__test/catalogo-gestion')->assertOk();
    }

    public function test_seeding_twice_is_idempotent(): void
    {
        $this->seed(StoreRolesPermissionsSeeder::class);
        $this->seed(StoreRolesPermissionsSeeder::class);

        $this->assertSame(4, Role::count());
    }
}
