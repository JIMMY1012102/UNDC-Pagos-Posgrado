<?php

namespace Tests\Feature\Modules;

use Tests\TestCase;

class StorefrontUiTest extends TestCase
{
    public function test_public_catalog_and_dedicated_cart_render_without_authentication(): void
    {
        $response = $this->get('/tienda');
        $response->assertOk()
            ->assertSee('Universidad Nacional de Cañete')
            ->assertSee('Admisión')
            ->assertSee('Servicios administrativos')
            ->assertSee('Carrito')
            ->assertSee('Tus trámites, mucho más cerca de ti.')
            ->assertSee('Nuestros trámites')
            ->assertSee('css/undc-tienda.css')
            ->assertSee('js/undc-tienda.js');

        // Since the UI was split into separate pages, checkout belongs in cart.
        $this->get('/tienda/carrito')->assertOk()
            ->assertSee('Revisa tu carrito')
            ->assertSee('Continuar con mi compra');
        $this->get('/tienda/login')->assertOk();
    }

    public function test_frontend_files_are_available_and_do_not_enable_unapproved_checkout(): void
    {
        $css = file_get_contents(public_path('css/undc-tienda.css'));
        $js = file_get_contents(public_path('js/undc-tienda.js'));

        $this->assertIsString($css);
        $this->assertIsString($js);
        $this->assertMatchesRegularExpression('/@media\\s*\\(max-width:\\s*640px\\)/', $css);
        $this->assertStringContainsString('/api/v1/tienda/carritos', $js);
        $this->assertStringContainsString('/api/v1/tienda/precompra/resumen', $js);
        $this->assertStringContainsString('/auth/google/redirect', $js);
        $this->assertStringNotContainsString('/api/v1/pagos/confirmar', $js);
    }
}
