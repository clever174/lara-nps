<?php

use App\Models\AudioGrade;
use App\Services\Ai\Providers\ProxyApiProvider;
use App\Services\AudioGrade\AudioGradeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

function makeAudioGradeService(): AudioGradeService
{
    return new AudioGradeService(
        new ProxyApiProvider(
            apiKey: 'test-api-key',
            defaultModel: 'gpt-4o-mini',
            defaultAudioModel: 'gemini-3.5-flash',
        ),
    );
}

it('extracts the url, downloads the file, creates the record, and stores the ai response', function () {
    Http::fake([
        'fs18.getcourse.ru/*' => Http::response('fake-audio-bytes', 200, ['Content-Type' => 'audio/wav']),
        'api.proxyapi.ru/google/*' => Http::response([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Analysis result']]]],
            ],
        ], 200),
    ]);

    $audioGrade = makeAudioGradeService()->handle([
        'user_id' => 42,
        'fio' => 'Иванов Иван',
        'attempt_number' => 1,
        'file_path' => '<a href="https://fs18.getcourse.ru/fileservice/file/download/a/950796/sc/3/h/403b9535d906f31466f2cf25a863a264.wav">Скачать</a>',
    ]);

    expect($audioGrade)->toBeInstanceOf(AudioGrade::class)
        ->and($audioGrade->user_id)->toBe(42)
        ->and($audioGrade->fio)->toBe('Иванов Иван')
        ->and($audioGrade->attempt_number)->toBe(1)
        ->and($audioGrade->file_path)->toBe('https://fs18.getcourse.ru/fileservice/file/download/a/950796/sc/3/h/403b9535d906f31466f2cf25a863a264.wav')
        ->and($audioGrade->ai_json)->toBe([
            'candidates' => [
                ['content' => ['parts' => [['text' => 'Analysis result']]]],
            ],
        ]);

    $this->assertDatabaseHas('audio_grades', [
        'id' => $audioGrade->id,
        'user_id' => 42,
        'file_path' => 'https://fs18.getcourse.ru/fileservice/file/download/a/950796/sc/3/h/403b9535d906f31466f2cf25a863a264.wav',
    ]);

    Http::assertSent(function ($request) {
        if (! str_contains($request->url(), 'proxyapi.ru')) {
            return false;
        }

        return $request['contents'][0]['parts'][1]['inline_data']['mime_type'] === 'audio/wav'
            && $request['contents'][0]['parts'][1]['inline_data']['data'] === base64_encode('fake-audio-bytes');
    });
});

it('throws when file_path markup has no href', function () {
    expect(fn () => makeAudioGradeService()->handle([
        'user_id' => 42,
        'fio' => 'Иванов Иван',
        'attempt_number' => 1,
        'file_path' => 'not html at all',
    ]))->toThrow(InvalidArgumentException::class);

    expect(AudioGrade::count())->toBe(0);
});

it('creates the record even if the AI call fails, leaving ai_json null', function () {
    Sleep::fake();

    Http::fake([
        'fs18.getcourse.ru/*' => Http::response('fake-audio-bytes', 200, ['Content-Type' => 'audio/wav']),
        'api.proxyapi.ru/google/*' => Http::response(['error' => 'boom'], 500),
    ]);

    expect(fn () => makeAudioGradeService()->handle([
        'user_id' => 42,
        'fio' => 'Иванов Иван',
        'attempt_number' => 1,
        'file_path' => '<a href="https://fs18.getcourse.ru/fileservice/file/download/a/1/h/x.wav">Скачать</a>',
    ]))->toThrow(\App\Services\Ai\Exceptions\AiException::class);

    $audioGrade = AudioGrade::first();
    expect($audioGrade)->not->toBeNull()
        ->and($audioGrade->ai_json)->toBeNull();
});
