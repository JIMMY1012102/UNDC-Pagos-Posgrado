<?php

namespace App\Modules\Asistente\Services;

use App\Modules\Asistente\Contracts\AssistantProvider;
use App\Modules\Asistente\Exceptions\AssistantUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class GeminiAssistantProvider implements AssistantProvider
{
    public function generate(string $message): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (! is_string($apiKey) || trim($apiKey) === '' || ! is_string($model) || trim($model) === '') {
            throw new AssistantUnavailable('El asistente no está configurado todavía. Inténtalo más tarde.');
        }

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

            $response->throw();

            $answer = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (! is_string($answer) || trim($answer) === '') {
                throw new AssistantUnavailable('El asistente no pudo generar una respuesta válida.');
            }

            return trim($answer);
        } catch (ConnectionException|RequestException $exception) {
            throw new AssistantUnavailable(
                'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.',
                previous: $exception,
            );
        }
    }
}
