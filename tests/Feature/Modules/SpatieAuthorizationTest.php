<?php

namespace Tests\Feature\Modules;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpatieAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_buyer_has_no_roles_or_permissions_by_default(): void
    {
        $buyer = User::factory()->create();

        $this->assertSame([], $buyer->getRoleNames()->all());
        $this->assertFalse($buyer->can('demo.consultar'));
    }

    public function test_roles_and_permissions_require_explicit_assignment(): void
    {
        $buyer = User::factory()->create();
        $role = Role::findOrCreate('rol_prueba_ci', 'web');
        $permission = Permission::findOrCreate('demo.consultar', 'web');

        $role->givePermissionTo($permission);
        $this->assertFalse($buyer->can('demo.consultar'));

        $buyer->assignRole($role);
        $this->assertTrue($buyer->fresh()->can('demo.consultar'));

        $anotherBuyer = User::factory()->create();
        $this->assertFalse($anotherBuyer->can('demo.consultar'));
    }
}
