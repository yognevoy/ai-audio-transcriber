<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranscriptCleanupService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = config('services.openai.chat_api_url');
        $this->model = config('services.openai.chat_model');
    }

    /**
     * Clean the transcription text from verbal noise and unnecessary words.
     *
     * @param string $text
     * @return string|null
     */
    public function cleanText(string $text): ?string
    {
        try {
            $prompt = "Clean up this transcription by removing verbal fillers, repetitions, and unnecessary words. "
                . "Keep the meaning intact but make it more readable and grammatically correct. "
                . "Remove phrases like 'um', 'uh', 'you know', 'so', 'like', 'basically', 'actually', 'kind of', 'sort of', etc. "
                . "Also remove stuttering and repeated words. Return only the cleaned text without any additional commentary:\n\n"
                . $text;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            } else {
                Log::error('Text Cleaning API Error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception during text cleaning API call: ' . $e->getMessage());
            return null;
        }
    }
}
