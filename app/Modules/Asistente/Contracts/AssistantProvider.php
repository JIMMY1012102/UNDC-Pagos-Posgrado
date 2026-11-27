<?php

namespace App\Modules\Asistente\Contracts;

interface AssistantProvider
{
    public function generate(string $message): string;
}
