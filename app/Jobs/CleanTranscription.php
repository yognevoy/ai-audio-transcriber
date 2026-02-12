<?php

namespace App\Jobs;

use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;
use App\Services\TranscriptCleanupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $audioFileId
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(TranscriptCleanupService $cleanupService): void
    {
        $audioFile = AudioFile::find($this->audioFileId);

        if (!$audioFile) {
            Log::error('Audio file not found ', [
                'audio_file_id' => $this->audioFileId
            ]);
            return;
        }

        try {
            $transcription = $audioFile->transcription;

            if (!$transcription) {
                throw new \Exception('Transcription not found for audio file');
            }

            $transcription->update([
                'status' => TranscriptionStatus::PROCESSING->value
            ]);

            $cleanedContent = $cleanupService->cleanText($transcription->raw_content);

            $transcription->update([
                'content' => $cleanedContent ?? $transcription->raw_content,
                'status' => TranscriptionStatus::COMPLETED->value
            ]);
        } catch (\Exception $e) {
            Log::error('Error cleaning transcription text: ' . $e->getMessage(), [
                'audio_file_id' => $this->audioFileId,
                'exception' => $e
            ]);

            if ($transcription) {
                $transcription->update([
                    'status' => TranscriptionStatus::FAILED->value,
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
}
