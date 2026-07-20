<?php

namespace App\Services\AudioGrade;

use App\Models\AudioGrade;
use App\Services\Ai\Providers\ProxyApiProvider;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class AudioGradeService
{
    private const SYSTEM_PROMPT = 'Наш системный промпт. Для теста пока просто проанализируй аудио запись. Потом его поменяем.';

    public function __construct(
        private readonly ProxyApiProvider $proxyApiProvider,
    ) {
    }

    public function handle(array $data): AudioGrade
    {
        $url = $this->extractFileUrl($data['file_path']);

        $fileResponse = Http::get($url);
        $audioData = $fileResponse->body();
        $mimeType = $fileResponse->header('Content-Type') ?: $this->guessMimeTypeFromUrl($url);

        $audioGrade = AudioGrade::create([
            'user_id' => $data['user_id'],
            'fio' => $data['fio'],
            'attempt_number' => $data['attempt_number'],
            'file_path' => $url,
        ]);

        $result = $this->proxyApiProvider->analyzeAudio(self::SYSTEM_PROMPT, $audioData, $mimeType);

        $audioGrade->update(['ai_json' => $result]);

        return $audioGrade;
    }

    private function extractFileUrl(string $markup): string
    {
        if (! preg_match('/href="([^"]+)"/', $markup, $matches)) {
            throw new InvalidArgumentException('Could not extract a file URL from file_path markup');
        }

        return $matches[1];
    }

    private function guessMimeTypeFromUrl(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'wav' => 'audio/wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            default => 'application/octet-stream',
        };
    }
}
