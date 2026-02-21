<?php

namespace Tests\Unit\Services;

use App\Services\TranscriptCleanupService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranscriptCleanupServiceTest extends TestCase
{
    protected TranscriptCleanupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.openai.api_key', 'test-api-key');
        Config::set('services.openai.chat_api_url', 'https://api.openai.com/v1/chat/completions');
        Config::set('services.openai.chat_model', 'gpt-3.5-turbo');

        $this->service = new TranscriptCleanupService();
    }

    public function test_clean_text_returns_cleaned_text_on_success(): void
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'This is cleaned text without fillers'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->cleanText('um uh like you know this is test');

        $this->assertSame('This is cleaned text without fillers', $result);
    }

    public function test_clean_text_returns_null_on_api_error(): void
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'error' => 'Rate limit exceeded'
            ], 429)
        ]);

        $result = $this->service->cleanText('test text');

        $this->assertNull($result);
    }

    public function test_clean_text_returns_null_on_malformed_response(): void
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => []
            ], 200)
        ]);

        $result = $this->service->cleanText('test text');

        $this->assertNull($result);
    }

    public function test_clean_text_sends_correct_request(): void
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Cleaned text'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $inputText = 'um uh test';
        $this->service->cleanText($inputText);

        Http::assertSent(fn($request) => $request->url() === 'https://api.openai.com/v1/chat/completions' &&
            $request->hasHeader('Authorization', 'Bearer test-api-key') &&
            $request->hasHeader('Content-Type', 'application/json')
        );
    }
}
