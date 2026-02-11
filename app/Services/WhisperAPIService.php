<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhisperAPIService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = config('services.openai.api_url');
    }

    /**
     * Transcribe audio file using OpenAI Whisper API.
     *
     * @param string $filePath
     * @param string $fileName
     * @param string $model
     * @return array|null
     */
    public function transcribe(string $filePath, string $fileName, string $model = 'whisper-1'): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->attach(
                'file',
                file_get_contents($filePath),
                $fileName
            )->post($this->apiUrl, [
                'model' => $model,
                'response_format' => 'verbose_json',
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Whisper API Error: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception during Whisper API call: ' . $e->getMessage());
            return null;
        }
    }
}
