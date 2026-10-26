<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles de la TIENDA Laravel exclusivamente (guard web).
 * No se crean usuarios, no se asignan roles privilegiados, no se tocan tm_*.
 * Matriz provisional, sujeta a aprobación OTI/Tesorería.
 */
final class StoreRolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $matrix = [
            'comprador' => [
                'pedidos.crear',
                'pedidos.ver-propios',
            ],
            'administrador-oficina' => [
                'catalogo.gestionar',
                'pedidos.ver-oficina',
            ],
            // No confiere acceso a SISPAGOS ni valida pagos.
            'tesoreria-consulta' => [
                'pagos.ver-tesoreria',
            ],
            // No implica autorización para confirmar cobros ni validar Tesorería.
            'superadministrador-tienda' => [
                'catalogo.gestionar',
                'pedidos.ver-oficina',
                'roles.gestionar',
            ],
        ];

        foreach (array_unique(array_merge(...array_values($matrix))) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach ($matrix as $name => $permissions) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            // Añade permisos base sin borrar asignaciones futuras aprobadas.
            $role->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
