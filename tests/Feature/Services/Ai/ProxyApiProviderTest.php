<?php

use App\Services\Ai\Exceptions\AiException;
use App\Services\Ai\Providers\ProxyApiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function makeProxyApiProvider(string $defaultModel = 'gpt-4o-mini', string $defaultAudioModel = 'gemini-3.5-flash'): ProxyApiProvider
{
    return new ProxyApiProvider(
        apiKey: 'test-api-key',
        defaultModel: $defaultModel,
        defaultAudioModel: $defaultAudioModel,
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

it('sends the expected request shape for audio analysis with the default audio model', function () {
    Http::fake([
        'api.proxyapi.ru/google/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Audio analysis result']]]],
            ],
        ], 200),
    ]);

    makeProxyApiProvider()->analyzeAudio('Analyze this', 'fake-audio-bytes', 'audio/wav');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.proxyapi.ru/google/v1beta/models/gemini-3.5-flash:generateContent'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request['contents'][0]['parts'][0]['text'] === 'Analyze this'
            && $request['contents'][0]['parts'][1]['inline_data']['mime_type'] === 'audio/wav'
            && $request['contents'][0]['parts'][1]['inline_data']['data'] === base64_encode('fake-audio-bytes');
    });
});

it('overrides the audio model via options', function () {
    Http::fake([
        'api.proxyapi.ru/google/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'ok']]]]],
        ], 200),
    ]);

    makeProxyApiProvider()->analyzeAudio('Analyze this', 'bytes', 'audio/wav', ['model' => 'gemini-2.5-pro']);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.proxyapi.ru/google/v1beta/models/gemini-2.5-pro:generateContent');
});

it('returns the full decoded json response on success', function () {
    Http::fake([
        'api.proxyapi.ru/google/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Audio analysis result']]]],
            ],
            'usageMetadata' => ['totalTokenCount' => 123],
        ], 200),
    ]);

    $result = makeProxyApiProvider()->analyzeAudio('Analyze this', 'bytes', 'audio/wav');

    expect($result)->toBe([
        'candidates' => [
            ['content' => ['parts' => [['text' => 'Audio analysis result']]]],
        ],
        'usageMetadata' => ['totalTokenCount' => 123],
    ]);
});

it('throws AiException on a failed audio analysis response', function () {
    Sleep::fake();

    Http::fake([
        'api.proxyapi.ru/google/*' => Http::response(['error' => str_repeat('x', 1000)], 400),
    ]);

    try {
        makeProxyApiProvider()->analyzeAudio('Analyze this', 'bytes', 'audio/wav');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('ProxyAPI API request failed with status 400')
            ->and($e->statusCode())->toBe(400)
            ->and(strlen($e->context()))->toBeLessThanOrEqual(300);
    }
});

it('throws AiException when a successful response is missing candidate text', function () {
    Http::fake([
        'api.proxyapi.ru/google/*' => Http::response(['candidates' => []], 200),
    ]);

    try {
        makeProxyApiProvider()->analyzeAudio('Analyze this', 'bytes', 'audio/wav');
        $this->fail('Expected AiException to be thrown');
    } catch (AiException $e) {
        expect($e->getMessage())->toBe('ProxyAPI response missing candidates.0.content.parts.0.text')
            ->and($e->statusCode())->toBe(200);
    }
});
