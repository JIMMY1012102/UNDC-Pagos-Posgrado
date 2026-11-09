<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketPagesTest extends TestCase
{
    public function test_catalog_does_not_render_cart_or_login_forms(): void
    {
        $this->get('/tienda')->assertOk()->assertViewIs('tienda')
            ->assertSee('id="tusneCatalog"', false)
            ->assertDontSee('id="cartRows"', false)
            ->assertDontSee('id="marketLoginEmail"', false);
    }

    public function test_cart_has_its_own_page_without_catalog_or_login(): void
    {
        $this->get('/tienda/carrito')->assertOk()->assertViewIs('modules.tienda.cart')
            ->assertSee('id="cartRows"', false)
            ->assertDontSee('id="tusneCatalog"', false)
            ->assertDontSee('id="marketLoginEmail"', false);
    }

    public function test_login_reuses_accessibility_without_cart_markup(): void
    {
        $this->get('/tienda/login')->assertOk()->assertViewIs('modules.acceso.login')
            ->assertSee('id="marketLoginEmail"', false)
            ->assertSee('id="accessibilityPanel"', false)
            ->assertDontSee('id="cartRows"', false)
            ->assertDontSee('id="tusneCatalog"', false);
    }

    public function test_old_login_link_redirects_to_dedicated_page(): void
    {
        $this->get('/tienda?login=1')->assertRedirect('/tienda/login');
    }
}
