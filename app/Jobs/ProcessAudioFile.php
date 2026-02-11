<?php

namespace App\Jobs;

use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Services\WhisperAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAudioFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AudioFile $audioFile;

    /**
     * Create a new job instance.
     */
    public function __construct(AudioFile $audioFile)
    {
        $this->audioFile = $audioFile;
    }

    /**
     * Execute the job.
     */
    public function handle(WhisperAPIService $whisperService): void
    {
        try {
            $this->audioFile->update(['status' => AudioFileStatus::PROCESSING->value]);

            $fullPath = storage_path('app/public/' . $this->audioFile->path);

            $result = $whisperService->transcribe($fullPath, $this->audioFile->filename);

            if ($result !== null) {
                Transcription::create([
                    'audio_file_id' => $this->audioFile->id,
                    'raw_content' => $result['text'] ?? '',
                    'status' => TranscriptionStatus::PROCESSING->value
                ]);

                $this->audioFile->update([
                    'status' => AudioFileStatus::COMPLETED->value,
                    'processed_at' => now()
                ]);
            } else {
                $this->audioFile->update([
                    'status' => AudioFileStatus::FAILED->value,
                    'error_message' => 'Transcription failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing audio file: ' . $e->getMessage(), [
                'audio_file_id' => $this->audioFile->id,
                'exception' => $e
            ]);

            $this->audioFile->update([
                'status' => AudioFileStatus::FAILED->value,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
