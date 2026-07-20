<?php

use App\Services\Ai\Exceptions\AiException;
use App\Services\Ai\Providers\ProxyApiProvider;
use App\Services\Ai\Providers\YandexGptProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

dataset('ai_providers', [
    'yandex' => [
        fn () => new YandexGptProvider(apiKey: 'k', folderId: 'f', model: 'm'),
        'llm.api.cloud.yandex.net/*',
        fn (string $text) => ['result' => ['alternatives' => [['message' => ['text' => $text]]]]],
    ],
    'proxyapi' => [
        fn () => new ProxyApiProvider(apiKey: 'k', defaultModel: 'gpt-4o-mini'),
        'api.proxyapi.ru/*',
        fn (string $text) => ['choices' => [['message' => ['content' => $text]]]],
    ],
]);

it('returns a non-empty string on a successful response', function (Closure $makeProvider, string $urlPattern, Closure $successBody) {
    Http::fake([
        $urlPattern => Http::response($successBody('contract test response'), 200),
    ]);

    $result = $makeProvider()->complete('any prompt');

    expect($result)->toBeString()->not->toBeEmpty();
})->with('ai_providers');

it('throws AiException on a failed response', function (Closure $makeProvider, string $urlPattern, Closure $successBody) {
    Sleep::fake();

    Http::fake([
        $urlPattern => Http::response(['error' => 'boom'], 500),
    ]);

    expect(fn () => $makeProvider()->complete('any prompt'))
        ->toThrow(AiException::class);
})->with('ai_providers');
