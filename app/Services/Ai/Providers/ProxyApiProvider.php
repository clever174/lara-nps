<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Exceptions\AiException;
use App\Services\Ai\Providers\Concerns\MakesAiHttpRequests;
use Illuminate\Support\Facades\Log;

class ProxyApiProvider implements AiProvider
{
    use MakesAiHttpRequests;

    private const ENDPOINT = 'https://api.proxyapi.ru/openai/v1/chat/completions';
    private const TRUNCATED_BODY_LENGTH = 300;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $defaultModel,
    ) {
    }

    public function complete(string $prompt, array $options = []): string
    {
        $startedAt = microtime(true);

        $body = [
            'model' => $options['model'] ?? $this->defaultModel,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if (array_key_exists('temperature', $options)) {
            $body['temperature'] = $options['temperature'];
        }

        if (array_key_exists('max_tokens', $options)) {
            $body['max_tokens'] = $options['max_tokens'];
        }

        $response = $this->postWithRetry(
            self::ENDPOINT,
            ['Authorization' => "Bearer {$this->apiKey}"],
            $body,
        );

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $text = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($text)) {
            throw new AiException(
                'ProxyAPI response missing choices.0.message.content',
                $response->status(),
                substr($response->body(), 0, self::TRUNCATED_BODY_LENGTH),
            );
        }

        Log::info('ai.proxyapi.completion', [
            'duration_ms' => $durationMs,
            'usage' => data_get($response->json(), 'usage'),
        ]);

        return $text;
    }

    protected function providerName(): string
    {
        return 'ProxyAPI';
    }
}
