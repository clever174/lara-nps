<?php

namespace App\Services\Ai\Contracts;

interface AiProvider
{
    /**
     * @param  array<string, mixed>  $options  Provider-specific overrides (e.g. temperature, max_tokens)
     */
    public function complete(string $prompt, array $options = []): string;
}
