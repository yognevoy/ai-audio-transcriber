<?php

namespace App\Jobs;

use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Services\AudioTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranscribeAudioFile implements ShouldQueue
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
    public function handle(AudioTranscriptionService $transcriptionService): void
    {
        $audioFile = AudioFile::find($this->audioFileId);

        if (!$audioFile) {
            Log::error('Audio file not found ', [
                'audio_file_id' => $this->audioFileId
            ]);
            return;
        }

        try {
            $audioFile->update(['status' => AudioFileStatus::PROCESSING->value]);

            $fullPath = storage_path('app/public/' . $audioFile->path);

            $result = $transcriptionService->transcribe($fullPath, $audioFile->filename);

            if ($result !== null) {
                Transcription::create([
                    'audio_file_id' => $audioFile->id,
                    'raw_content' => $result['text'] ?? '',
                    'status' => TranscriptionStatus::PROCESSING->value
                ]);

                $audioFile->update([
                    'status' => AudioFileStatus::COMPLETED->value,
                    'processed_at' => now()
                ]);
            } else {
                $audioFile->update([
                    'status' => AudioFileStatus::FAILED->value,
                    'error_message' => 'Transcription failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing audio file: ' . $e->getMessage(), [
                'audio_file_id' => $audioFile->id,
                'exception' => $e
            ]);

            $audioFile->update([
                'status' => AudioFileStatus::FAILED->value,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
