<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Providers\ProxyApiProvider;
use App\Services\Ai\Providers\YandexGptProvider;
use InvalidArgumentException;

class AiProviderFactory
{
    public function make(): AiProvider
    {
        return match (config('ai.provider')) {
            'yandex' => new YandexGptProvider(
                apiKey: config('ai.providers.yandex.api_key'),
                folderId: config('ai.providers.yandex.folder_id'),
                model: config('ai.providers.yandex.model'),
            ),
            'proxyapi' => new ProxyApiProvider(
                apiKey: config('ai.providers.proxyapi.api_key'),
                defaultModel: config('ai.providers.proxyapi.model'),
            ),
            default => throw new InvalidArgumentException(
                'Unknown AI provider ['.config('ai.provider').']'
            ),
        };
    }
}
