<?php

namespace App\Modules\Asistente\Services;

use App\Modules\Asistente\Contracts\AssistantProvider;

final class AssistantService
{
    public function __construct(private readonly AssistantProvider $provider)
    {
    }

    public function reply(string $message): string
    {
        return $this->provider->generate($message);
    }
}
