<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketHomeNavigationTest extends TestCase
{
    public function test_store_home_contains_discovery_sections(): void
    {
        $this->get('/tienda')->assertOk()
            ->assertSee('id="marketHome"', false)
            ->assertSee('Elige, revisa y paga tu pedido')
            ->assertSee('id="homeStory"', false);
    }

    public function test_catalogue_is_a_separate_screen(): void
    {
        $this->get('/tienda/catalogo')->assertOk()
            ->assertSee('id="tusneResults"', false)
            ->assertDontSee('id="marketHome"', false)
            ->assertDontSee('id="hero"', false);
    }

    public function test_old_search_links_keep_their_filters(): void
    {
        $this->get('/tienda?q=matricula&category=matricula_pensiones')
            ->assertRedirect('/tienda/catalogo?category=matricula_pensiones&q=matricula');
    }

    public function test_old_login_shortcut_still_works(): void
    {
        $this->get('/tienda?login=1')->assertRedirect(route('tienda.login'));
    }
}
