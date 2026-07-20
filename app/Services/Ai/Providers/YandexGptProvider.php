<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\AiProvider;
use App\Services\Ai\Exceptions\AiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexGptProvider implements AiProvider
{
    private const ENDPOINT = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completion';
    private const DEFAULT_TEMPERATURE = 0.6;
    private const DEFAULT_MAX_TOKENS = 2000;
    private const TRUNCATED_BODY_LENGTH = 300;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $folderId,
        private readonly string $model,
    ) {
    }

    public function complete(string $prompt, array $options = []): string
    {
        $startedAt = microtime(true);

        $response = Http::withHeaders([
            'Authorization' => "Api-Key {$this->apiKey}",
        ])
            ->timeout(60)
            ->retry(3, 200, throw: false)
            ->post(self::ENDPOINT, [
                'modelUri' => "gpt://{$this->folderId}/{$this->model}",
                'completionOptions' => [
                    'stream' => false,
                    'temperature' => $options['temperature'] ?? self::DEFAULT_TEMPERATURE,
                    'maxTokens' => (string) ($options['max_tokens'] ?? self::DEFAULT_MAX_TOKENS),
                ],
                'messages' => [
                    ['role' => 'user', 'text' => $prompt],
                ],
            ]);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($response->failed()) {
            throw new AiException(
                "Yandex API request failed with status {$response->status()}",
                $response->status(),
                substr($response->body(), 0, self::TRUNCATED_BODY_LENGTH),
            );
        }

        $text = data_get($response->json(), 'result.alternatives.0.message.text');

        if (! is_string($text)) {
            throw new AiException(
                'Yandex API response missing result.alternatives.0.message.text',
                $response->status(),
                substr($response->body(), 0, self::TRUNCATED_BODY_LENGTH),
            );
        }

        Log::info('ai.yandex.completion', [
            'duration_ms' => $durationMs,
            'usage' => data_get($response->json(), 'result.usage'),
        ]);

        return $text;
    }
}
