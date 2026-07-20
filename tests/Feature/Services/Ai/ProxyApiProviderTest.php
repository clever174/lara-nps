<?php

use App\Services\Ai\Exceptions\AiException;
use App\Services\Ai\Providers\ProxyApiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function makeProxyApiProvider(string $defaultModel = 'gpt-4o-mini'): ProxyApiProvider
{
    return new ProxyApiProvider(
        apiKey: 'test-api-key',
        defaultModel: $defaultModel,
    );
}

it('sends the expected request shape with the default model', function () {
    Http::fake([
        'api.proxyapi.ru/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Hello from ProxyAPI']],
            ],
        ], 200),
    ]);

    makeProxyApiProvider()->complete('Say hello');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.proxyapi.ru/openai/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request['model'] === 'gpt-4o-mini'
            && $request['messages'] === [
                ['role' => 'user', 'content' => 'Say hello'],
            ]
            && ! array_key_exists('temperature', $request->data())
            && ! array_key_exists('max_tokens', $request->data());
    });
});

it('overrides the model via options', function () {
    Http::fake([
        'api.proxyapi.ru/*' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ], 200),
    ]);

    makeProxyApiProvider()->complete('Say hello', ['model' => 'gpt-4o']);

    Http::assertSent(fn ($request) => $request['model'] === 'gpt-4o');
});

it('includes temperature and max_tokens only when passed in options', function () {
    Http::fake([
        'api.proxyapi.ru/*' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
        ], 200),
    ]);

    makeProxyApiProvider()->complete('Say hello', ['temperature' => 0.2, 'max_tokens' => 300]);

    Http::assertSent(fn ($request) => $request['temperature'] === 0.2 && $request['max_tokens'] === 300);
});

it('returns the completion text on a successful response', function () {
    Http::fake([
        'api.proxyapi.ru/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Hello from ProxyAPI']],
            ],
        ], 200),
    ]);

    $result = makeProxyApiProvider()->complete('Say hello');

    expect($result)->toBe('Hello from ProxyAPI');
});

it('throws AiException with a truncated, non-message-embedded body on a failed response', function () {
    Sleep::fake();

    Http::fake([
        'api.proxyapi.ru/*' => Http::response(['error' => str_repeat('x', 1000)], 400),
    ]);

    try {
        makeProxyApiProvider()->complete('Say hello');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('ProxyAPI API request failed with status 400')
            ->and($e->getMessage())->not->toContain('xxx')
            ->and($e->statusCode())->toBe(400)
            ->and(strlen($e->context()))->toBeLessThanOrEqual(300);
    }
});

it('throws AiException on connection failure', function () {
    Sleep::fake();

    Http::fake([
        'api.proxyapi.ru/*' => fn () => throw new ConnectionException('Connection refused'),
    ]);

    try {
        makeProxyApiProvider()->complete('Say hello');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('ProxyAPI API request failed: connection error')
            ->and($e->statusCode())->toBe(0);
    }
});

it('retries on a 503 before succeeding', function () {
    Sleep::fake();

    Http::fake([
        'api.proxyapi.ru/*' => Http::sequence()
            ->push(['error' => 'unavailable'], 503)
            ->push([
                'choices' => [['message' => ['content' => 'ok after retry']]],
            ], 200),
    ]);

    $result = makeProxyApiProvider()->complete('Say hello');

    expect($result)->toBe('ok after retry');
    Http::assertSentCount(2);
});
