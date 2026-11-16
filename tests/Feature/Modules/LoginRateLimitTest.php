<?php

namespace Tests\Feature\Modules;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    public function test_browsing_does_not_consume_login_attempts(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.51']);
        Route::middleware(['web', 'throttle:60,1'])->get('/_test/browse', fn () => response('ok'));
        for ($i = 0; $i < 12; $i++) {
            $this->get('/_test/browse')->assertOk();
        }

        // Invalid empty input reaches validation, rather than being rate limited.
        $this->from('/tienda/login')->post('/tienda/login', [])
            ->assertRedirect('/tienda/login')
            ->assertSessionHasErrors(['email', 'password'])
            ->assertSessionMissing('_old_input.password');
    }

    public function test_limit_keeps_login_page_available_and_never_flashes_password(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.52']);
        for ($i = 0; $i < 10; $i++) {
            $this->from('/tienda/login')->post('/tienda/login', [])
                ->assertRedirect('/tienda/login');
        }

        $this->post('/tienda/login', ['email' => 'demo@example.test', 'password' => 'never-flash-this'])
            ->assertRedirect('/tienda/login')
            ->assertSessionHasErrors(['email' => 'Has realizado varios intentos de acceso. Espera un minuto y vuelve a intentarlo.'])
            ->assertSessionMissing('_old_input.password');
        $before = session('errors')?->all();
        $response = $this->get('/tienda/login')->assertOk();
        preg_match('/<div class="warning"[\s\S]*?<\/div>/', $response->getContent(), $alert);
        if (! str_contains($response->getContent(), 'Espera un minuto')) throw new \RuntimeException(json_encode([
            'before' => $before, 'after' => session('errors')?->all(), 'alert' => $alert[0] ?? null,
        ]));
    }
}
