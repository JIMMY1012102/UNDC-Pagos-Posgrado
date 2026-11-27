<?php

namespace Tests\Feature\Modules;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    public function test_message_endpoint_uses_the_configured_gemini_provider_without_csrf(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'test-model',
            'services.gemini.fallback_models' => [],
            'services.gemini.fallback_model' => null,
        ]);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/test-model:generateContent';

        Http::fake([
            $url => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Hola, ¿en qué puedo ayudarte?']]],
                ]],
            ]),
        ]);

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.answer', 'Hola, ¿en qué puedo ayudarte?');

        Http::assertSent(fn (Request $request): bool => $request->url() === $url
            && $request->hasHeader('x-goog-api-key', 'test-api-key')
            && data_get($request->data(), 'contents.0.parts.0.text') === 'Hola');
    }

    public function test_provider_tries_models_in_configured_order_until_one_answers(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_models' => ['backup-one', 'backup-two', 'backup-three'],
            'services.gemini.fallback_model' => null,
        ]);

        $attemptedModels = [];

        Http::fake(function (Request $request) use (&$attemptedModels) {
            $path = explode('/models/', $request->url(), 2)[1] ?? '';
            $model = explode(':', $path, 2)[0];
            $attemptedModels[] = $model;

            if ($model === 'primary-model') {
                return Http::response(['error' => ['message' => 'quota exceeded']], 429);
            }

            if ($model === 'backup-one') {
                return Http::response(['error' => ['message' => 'temporarily unavailable']], 503);
            }

            if ($model === 'backup-two') {
                return Http::response(['error' => ['message' => 'model unavailable']], 404);
            }

            return Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Respondió el cuarto modelo.']]],
                ]],
            ]);
        });

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertOk()
            ->assertJsonPath('data.answer', 'Respondió el cuarto modelo.');

        $this->assertSame(
            ['primary-model', 'backup-one', 'backup-two', 'backup-three'],
            $attemptedModels,
        );
        Http::assertSentCount(4);
    }

    public function test_legacy_single_fallback_model_remains_supported(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_models' => [],
            'services.gemini.fallback_model' => 'legacy-fallback',
        ]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/primary-model:')) {
                return Http::response(['error' => ['message' => 'temporarily unavailable']], 503);
            }

            return Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Respuesta heredada.']]],
                ]],
            ]);
        });

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertOk()
            ->assertJsonPath('data.answer', 'Respuesta heredada.');

        Http::assertSentCount(2);
    }

    public function test_authentication_errors_do_not_consume_fallback_models(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_models' => ['backup-one', 'backup-two'],
            'services.gemini.fallback_model' => null,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'invalid API key']], 401),
        ]);

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false);

        Http::assertSentCount(1);
    }

    public function test_connection_failure_returns_without_repeating_the_same_remote_request(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_models' => ['backup-one', 'backup-two', 'backup-three'],
            'services.gemini.fallback_model' => null,
        ]);

        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;

            throw new ConnectionException('connection unavailable');
        });

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No se pudo conectar con el servicio del asistente. Comprueba la conexión e inténtalo nuevamente.');

        $this->assertSame(1, $attempts);
    }

    public function test_message_is_required_and_has_a_maximum_length(): void
    {
        Http::fake();

        $this->postJson('/api/v1/asistente/mensajes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        $this->postJson('/api/v1/asistente/mensajes', ['message' => str_repeat('a', 4001)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        Http::assertNothingSent();
    }

    public function test_all_models_failing_returns_a_friendly_service_unavailable(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_models' => ['backup-one', 'backup-two'],
            'services.gemini.fallback_model' => null,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'private provider detail']], 503),
        ]);

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.')
            ->assertDontSee('private provider detail');

        Http::assertSentCount(3);
    }

    public function test_missing_gemini_configuration_returns_service_unavailable_without_http_call(): void
    {
        config([
            'services.gemini.api_key' => null,
            'services.gemini.model' => null,
            'services.gemini.fallback_models' => [],
            'services.gemini.fallback_model' => null,
        ]);
        Http::fake();

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }
}
