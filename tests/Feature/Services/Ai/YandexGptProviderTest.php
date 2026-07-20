<?php

use App\Services\Ai\Exceptions\AiException;
use App\Services\Ai\Providers\YandexGptProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function makeYandexProvider(): YandexGptProvider
{
    return new YandexGptProvider(
        apiKey: 'test-api-key',
        folderId: 'test-folder-id',
        model: 'yandexgpt-lite/latest',
    );
}

it('sends the expected request shape to the Yandex completion endpoint', function () {
    Http::fake([
        'llm.api.cloud.yandex.net/*' => Http::response([
            'result' => [
                'alternatives' => [
                    ['message' => ['text' => 'Hello from Yandex']],
                ],
            ],
        ], 200),
    ]);

    makeYandexProvider()->complete('Say hello');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion'
            && $request->hasHeader('Authorization', 'Api-Key test-api-key')
            && $request['modelUri'] === 'gpt://test-folder-id/yandexgpt-lite/latest'
            && $request['completionOptions'] === [
                'stream' => false,
                'temperature' => 0.6,
                'maxTokens' => '2000',
            ]
            && $request['messages'] === [
                ['role' => 'user', 'text' => 'Say hello'],
            ];
    });
});

it('returns the completion text on a successful response', function () {
    Http::fake([
        'llm.api.cloud.yandex.net/*' => Http::response([
            'result' => [
                'alternatives' => [
                    ['message' => ['text' => 'Hello from Yandex']],
                ],
            ],
        ], 200),
    ]);

    $result = makeYandexProvider()->complete('Say hello');

    expect($result)->toBe('Hello from Yandex');
});

it('applies temperature and max_tokens overrides from options', function () {
    Http::fake([
        'llm.api.cloud.yandex.net/*' => Http::response([
            'result' => ['alternatives' => [['message' => ['text' => 'ok']]]],
        ], 200),
    ]);

    makeYandexProvider()->complete('Say hello', ['temperature' => 0.1, 'max_tokens' => 500]);

    Http::assertSent(function ($request) {
        return $request['completionOptions']['temperature'] === 0.1
            && $request['completionOptions']['maxTokens'] === '500';
    });
});

it('throws AiException with a truncated, non-message-embedded body on a failed response', function () {
    Sleep::fake();

    Http::fake([
        'llm.api.cloud.yandex.net/*' => Http::response(['error' => str_repeat('x', 1000)], 400),
    ]);

    try {
        makeYandexProvider()->complete('Say hello');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('Yandex API request failed with status 400')
            ->and($e->getMessage())->not->toContain('xxx')
            ->and($e->statusCode())->toBe(400)
            ->and(strlen($e->truncatedBody()))->toBeLessThanOrEqual(300);
    }
});

it('retries on a 503 before succeeding', function () {
    Sleep::fake();

    Http::fake([
        'llm.api.cloud.yandex.net/*' => Http::sequence()
            ->push(['error' => 'unavailable'], 503)
            ->push([
                'result' => ['alternatives' => [['message' => ['text' => 'ok after retry']]]],
            ], 200),
    ]);

    $result = makeYandexProvider()->complete('Say hello');

    expect($result)->toBe('ok after retry');
    Http::assertSentCount(2);
});

it('throws AiException on connection failure', function () {
    Sleep::fake();

    Http::fake([
        'llm.api.cloud.yandex.net/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'),
    ]);

    try {
        makeYandexProvider()->complete('Say hello');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('Yandex API request failed: connection error')
            ->and($e->statusCode())->toBe(0);
    }
});
