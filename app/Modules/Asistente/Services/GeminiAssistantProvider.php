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

        $configuredFallbacks = config('services.gemini.fallback_models', []);
        $models = array_merge(
            [$primaryModel],
            is_array($configuredFallbacks) ? $configuredFallbacks : [],
            [config('services.gemini.fallback_model')],
        );

        $models = array_values(array_unique(array_filter(
            array_map(static fn ($model): string => is_string($model) ? trim($model) : '', $models),
            static fn (string $model): bool => $model !== '',
        )));

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
            } catch (ConnectionException) {
                // Todos los modelos usan el mismo servicio remoto: si el servidor
                // no puede conectarse, cambiar de modelo solo repetiría el timeout.
                throw new AssistantUnavailable(
                    'No se pudo conectar con el servicio del asistente. Comprueba la conexión e inténtalo nuevamente.',
                );
            }

            // Un modelo alternativo puede atender la solicitud si el principal
            // no existe en este proyecto, está limitado o su servicio está caído.
            // Los errores de autenticación y de solicitud no se corrigen cambiando
            // de modelo, por lo que se devuelven sin consumir los respaldos.
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
