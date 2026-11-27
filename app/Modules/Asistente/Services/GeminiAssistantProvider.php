<?php

namespace App\Modules\Asistente\Services;

use App\Modules\Asistente\Contracts\AssistantProvider;
use App\Modules\Asistente\Exceptions\AssistantUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class GeminiAssistantProvider implements AssistantProvider
{
    public function generate(string $message): string
    {
        $apiKey = config('services.gemini.api_key');
        $primaryModel = config('services.gemini.model');

        if (! is_string($apiKey) || trim($apiKey) === '' || ! is_string($primaryModel) || trim($primaryModel) === '') {
            throw new AssistantUnavailable('El asistente no está configurado todavía. Inténtalo más tarde.');
        }

        $models = array_values(array_unique(array_filter([
            trim($primaryModel),
            config('services.gemini.fallback_model'),
        ], fn ($model): bool => is_string($model) && trim($model) !== '')));

        foreach ($models as $model) {
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'
                .rawurlencode($model).':generateContent';

            try {
                $response = Http::acceptJson()
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->timeout(20)
                    ->post($endpoint, [
                        'contents' => [[
                            'role' => 'user',
                            'parts' => [['text' => $message]],
                        ]],
                    ]);
            } catch (ConnectionException $exception) {
                continue;
            }

            // Reintentar con el modelo alternativo ante indisponibilidad,
            // límite de cuota o modelo inexistente. Errores de credenciales
            // y solicitudes inválidas no se solucionan cambiando de modelo.
            if (in_array($response->status(), [404, 408, 429], true) || $response->serverError()) {
                continue;
            }

            if (! $response->successful()) {
                throw new AssistantUnavailable(
                    'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.',
                );
            }

            $answer = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (is_string($answer) && trim($answer) !== '') {
                return trim($answer);
            }
        }

        throw new AssistantUnavailable(
            'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.',
        );
    }
}
