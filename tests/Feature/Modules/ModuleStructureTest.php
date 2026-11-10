<?php

namespace Tests\Feature\Modules;

use App\Modules\Usuarios\Http\Controllers\AccountController;
use App\Modules\Usuarios\Http\Controllers\BuyerProfileController;
use App\Modules\Usuarios\Http\Controllers\GoogleAuthController;
use Tests\TestCase;

class ModuleStructureTest extends TestCase
{
    public function test_each_business_module_has_an_independent_route_file(): void
    {
        foreach (['Sispagos', 'Catalogo', 'Usuarios', 'Pedidos', 'Pagos', 'Tesoreria', 'Seguridad'] as $module) {
            $this->assertFileExists(app_path("Modules/{$module}/routes.php"));
        }
    }

    public function test_homepage_still_works_before_the_store_is_implemented(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_existing_account_routes_resolve_to_the_usuarios_module(): void
    {
        $actions = [
            'tienda.registro' => [AccountController::class, 'registerForm'],
            'tienda.registro.store' => [AccountController::class, 'register'],
            'tienda.login.store' => [AccountController::class, 'login'],
            'tienda.perfil' => [AccountController::class, 'profileForm'],
            'tienda.perfil.store' => [AccountController::class, 'saveProfile'],
            'api.acceso.estado' => [BuyerProfileController::class, 'state'],
            'api.acceso.perfil.show' => [BuyerProfileController::class, 'show'],
            'api.acceso.perfil.update' => [BuyerProfileController::class, 'update'],
            'auth.google.redirect' => [GoogleAuthController::class, 'redirect'],
            'auth.google.callback' => [GoogleAuthController::class, 'callback'],
            'auth.logout' => [GoogleAuthController::class, 'logout'],
        ];

        foreach ($actions as $name => [$controller, $method]) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertNotNull($route, $name);
            $this->assertSame($controller.'@'.$method, $route->getActionName(), $name);
            $this->assertInstanceOf($controller, app($controller));
        }
    }
}
