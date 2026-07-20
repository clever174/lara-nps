<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('creates an audio grade and returns it with the ai response', function () {
    Config::set('api.key', 'test-secret-key');
    Config::set('ai.providers.proxyapi', [
        'api_key' => 'test-proxy-key',
        'model' => 'gpt-4o-mini',
        'audio_model' => 'gemini-3.5-flash',
    ]);

    Http::fake([
        'fs18.getcourse.ru/*' => Http::response('fake-audio-bytes', 200, ['Content-Type' => 'audio/wav']),
        'api.proxyapi.ru/google/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Analysis result']]]],
            ],
        ], 200),
    ]);

    $response = $this->withHeaders(['X-API-Key' => 'test-secret-key'])
        ->postJson('/api/audio-grades', [
            'user_id' => 42,
            'fio' => 'Иванов Иван',
            'attempt_number' => 1,
            'file_path' => '<a href="https://fs18.getcourse.ru/fileservice/file/download/a/1/h/x.wav">Скачать</a>',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('user_id', 42)
        ->assertJsonPath('file_path', 'https://fs18.getcourse.ru/fileservice/file/download/a/1/h/x.wav')
        ->assertJsonPath('ai_json.candidates.0.content.parts.0.text', 'Analysis result');

    $this->assertDatabaseCount('audio_grades', 1);
});

it('rejects requests without a valid api key', function () {
    Config::set('api.key', 'test-secret-key');

    $response = $this->postJson('/api/audio-grades', [
        'user_id' => 42,
        'fio' => 'Иванов Иван',
        'attempt_number' => 1,
        'file_path' => '<a href="https://example.com/a.wav">Скачать</a>',
    ]);

    $response->assertStatus(401);
    $this->assertDatabaseCount('audio_grades', 0);
});

it('validates required fields', function () {
    Config::set('api.key', 'test-secret-key');

    $response = $this->withHeaders(['X-API-Key' => 'test-secret-key'])
        ->postJson('/api/audio-grades', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id', 'fio', 'attempt_number', 'file_path']);
});
