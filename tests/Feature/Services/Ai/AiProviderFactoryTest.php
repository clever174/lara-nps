<?php

use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Providers\YandexGptProvider;
use Illuminate\Support\Facades\Config;

function configureYandexProvider(): void
{
    Config::set('ai.provider', 'yandex');
    Config::set('ai.providers.yandex', [
        'api_key' => 'test-key',
        'folder_id' => 'test-folder',
        'model' => 'yandexgpt-lite/latest',
    ]);
}

it('resolves the yandex provider when configured', function () {
    configureYandexProvider();

    $provider = (new AiProviderFactory())->make();

    expect($provider)->toBeInstanceOf(YandexGptProvider::class);
});

it('throws for an unknown provider', function () {
    Config::set('ai.provider', 'bogus');

    expect(fn () => (new AiProviderFactory())->make())
        ->toThrow(InvalidArgumentException::class);
});

it('resolves AiProvider via the container binding', function () {
    configureYandexProvider();

    expect(app(AiProvider::class))->toBeInstanceOf(YandexGptProvider::class);
});
