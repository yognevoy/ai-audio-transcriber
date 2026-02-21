<?php

namespace Tests\Unit\Services;

use App\Services\AudioTranscriptionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AudioTranscriptionServiceTest extends TestCase
{
    protected AudioTranscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openai.api_key', 'test-api-key');
        Config::set('services.openai.whisper_api_url', 'https://api.openai.com/v1/audio/transcriptions');
        Config::set('services.openai.whisper_model', 'whisper-1');

        $this->service = new AudioTranscriptionService();
    }

    public function test_transcribe_returns_result_on_success(): void
    {
        Http::fake([
            'api.openai.com/v1/audio/transcriptions' => Http::response([
                'text' => 'Hello world this is a test transcription'
            ], 200)
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'audio_test');
        file_put_contents($tempFile, 'fake audio content');

        $result = $this->service->transcribe($tempFile, 'test.mp3');

        $this->assertNotNull($result);
        $this->assertSame('Hello world this is a test transcription', $result['text']);

        unlink($tempFile);
    }

    public function test_transcribe_returns_null_on_api_error(): void
    {
        Http::fake([
            'api.openai.com/v1/audio/transcriptions' => Http::response([
                'error' => 'Invalid API key'
            ], 401)
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'audio_test');
        file_put_contents($tempFile, 'fake audio content');

        $result = $this->service->transcribe($tempFile, 'test.mp3');

        $this->assertNull($result);

        unlink($tempFile);
    }

    public function test_transcribe_returns_null_on_exception(): void
    {
        $result = $this->service->transcribe('/nonexistent/path/file.mp3', 'test.mp3');

        $this->assertNull($result);
    }

    public function test_transcribe_sends_correct_request(): void
    {
        Http::fake([
            'api.openai.com/v1/audio/transcriptions' => Http::response([
                'text' => 'Test response'
            ], 200)
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'audio_test');
        file_put_contents($tempFile, 'fake audio content');

        $this->service->transcribe($tempFile, 'test.mp3');

        Http::assertSent(fn($request) => $request->url() === 'https://api.openai.com/v1/audio/transcriptions' &&
            $request->hasHeader('Authorization', 'Bearer test-api-key')
        );

        unlink($tempFile);
    }
}
