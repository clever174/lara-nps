<?php

namespace App\Services\Ai\Providers\Concerns;

use App\Services\Ai\Exceptions\AiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait MakesAiHttpRequests
{
    abstract protected function providerName(): string;

    protected function postWithRetry(string $url, array $headers, array $body): Response
    {
        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->retry(3, 200, throw: false)
                ->post($url, $body);
        } catch (ConnectionException $e) {
            throw new AiException(
                "{$this->providerName()} API request failed: connection error",
                0,
                substr($e->getMessage(), 0, 300),
            );
        }

        if ($response->failed()) {
            throw new AiException(
                "{$this->providerName()} API request failed with status {$response->status()}",
                $response->status(),
                substr($response->body(), 0, 300),
            );
        }

        return $response;
    }
}
