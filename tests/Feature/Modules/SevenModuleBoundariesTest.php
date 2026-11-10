<?php

namespace Tests\Feature\Modules;

use App\Modules\Catalogo\Contracts\CatalogItemLookup;
use App\Modules\Catalogo\Services\CatalogItemLookupService;
use App\Modules\Pedidos\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SevenModuleBoundariesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_seven_modules_are_real_and_catalog_contract_is_resolvable(): void
    {
        foreach (['Sispagos', 'Catalogo', 'Usuarios', 'Pedidos', 'Pagos', 'Tesoreria', 'Seguridad'] as $module) {
            $this->assertFileExists(app_path("Modules/{$module}/routes.php"));
        }

        $this->assertInstanceOf(CatalogItemLookupService::class, app(CatalogItemLookup::class));
        $this->assertNotNull(app('router')->getRoutes()->getByName('api.tienda.catalogo.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('api.tienda.carritos.add'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('api.acceso.perfil.show'));
    }

    public function test_pedidos_asks_catalogo_instead_of_querying_the_legacy_tables_directly(): void
    {
        $this->app->instance(CatalogItemLookup::class, new class implements CatalogItemLookup {
            public function isAvailable(int $catalogItemId): bool
            {
                return false;
            }
        });

        $service = app(CartService::class);
        $created = $service->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CATALOG_ITEM_UNAVAILABLE');

        // This test has no tm_* fixtures: an old direct SQL join would fail here.
        $service->add($created['token'], 999, 1);
    }

    public function test_no_laravel_migration_modifies_the_sispagos_schema(): void
    {
        foreach (glob(database_path('migrations/*.php')) as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertDoesNotMatchRegularExpression(
                '/Schema::(?:create|table|drop|rename|dropIfExists)\s*\(\s*[\x27"]tm_/i',
                $contents,
                "SISPAGOS table modified in {$file}"
            );
        }
    }
}
