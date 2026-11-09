<?php

namespace Tests\Feature\Modules;

use Tests\TestCase;

class ModuleStructureTest extends TestCase
{
    public function test_each_business_module_has_an_independent_route_file(): void
    {
        foreach (['Sispagos', 'Catalogo', 'Compradores', 'Pedidos', 'Pagos', 'Tesoreria', 'Seguridad'] as $module) {
            $this->assertFileExists(app_path("Modules/{$module}/routes.php"));
        }
    }

    public function test_homepage_still_works_before_the_store_is_implemented(): void
    {
        $this->get('/')->assertOk();
    }
}
