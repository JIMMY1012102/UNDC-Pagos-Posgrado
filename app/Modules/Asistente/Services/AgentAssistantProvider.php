<?php

namespace App\Modules\Asistente\Services;

use App\Modules\Asistente\Contracts\AssistantProvider;
use App\Modules\Asistente\Exceptions\AssistantUnavailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

final class AgentAssistantProvider implements AssistantProvider
{
    public function __construct(private readonly GeminiAssistantProvider $legacyProvider)
    {
    }

    public function generate(string $message): string
    {
        $url = rtrim((string) config('services.assistant_agent.url'), '/');
        $token = (string) config('services.assistant_agent.token');

        // Keep the current Gemini HTTP adapter working until the Python service
        // and its private shared token have been configured in this environment.
        if ($url === '' || trim($token) === '') {
            return $this->legacyProvider->generate($message);
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(75)
                ->post($url.'/messages', ['message' => $message]);
        } catch (ConnectionException) {
            throw new AssistantUnavailable(
                'No se pudo conectar con el agente del asistente. Inténtalo nuevamente más tarde.',
            );
        }

        if (! $response->successful()) {
            throw new AssistantUnavailable(
                'El asistente no está disponible en este momento. Inténtalo nuevamente más tarde.',
            );
        }

        $answer = $response->json('answer');

        if (! is_string($answer) || trim($answer) === '') {
            throw new AssistantUnavailable('El asistente no pudo generar una respuesta válida.');
        }

        return trim($answer);
    }
}
