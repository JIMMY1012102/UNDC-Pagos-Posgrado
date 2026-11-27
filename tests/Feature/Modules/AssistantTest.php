<?php

namespace Tests\Feature\Modules;

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

    public function test_primary_model_failure_uses_the_configured_fallback_model(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'primary-model',
            'services.gemini.fallback_model' => 'fallback-model',
        ]);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/primary-model:')) {
                return Http::response(['error' => ['message' => 'temporarily unavailable']], 503);
            }

            return Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Respuesta desde el modelo alternativo.']]],
                ]],
            ]);
        });

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertOk()
            ->assertJsonPath('data.answer', 'Respuesta desde el modelo alternativo.');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/primary-model:'));
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/fallback-model:'));
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

    public function test_provider_http_errors_are_returned_as_friendly_service_unavailable(): void
    {
        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'test-model',
            'services.gemini.fallback_model' => null,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'private provider detail']], 500),
        ]);

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.')
            ->assertDontSee('private provider detail');
    }

    public function test_missing_gemini_configuration_returns_service_unavailable_without_http_call(): void
    {
        config([
            'services.gemini.api_key' => null,
            'services.gemini.model' => null,
            'services.gemini.fallback_model' => null,
        ]);
        Http::fake();

        $this->postJson('/api/v1/asistente/mensajes', ['message' => 'Hola'])
            ->assertStatus(503)
            ->assertJsonPath('success', false);

        Http::assertNothingSent();
    }
}
