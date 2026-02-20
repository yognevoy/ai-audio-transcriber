<?php

namespace App\Services;

use App\Dto\ProcessingStatusDto;
use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;

class ProcessingStatusResolver
{
    /**
     * Resolve the current processing status of the audio file.
     *
     * @param AudioFile $audioFile
     * @return ProcessingStatusDto
     */
    public function resolve(AudioFile $audioFile): ProcessingStatusDto
    {
        return match ($audioFile->status) {

            AudioFileStatus::FAILED->value =>
            new ProcessingStatusDto(
                progress: 0,
                isFinished: true,
                isSuccessful: false,
                errorMessage: $audioFile->error_message
            ),

            AudioFileStatus::PROCESSING->value =>
            new ProcessingStatusDto(50, false, false),

            AudioFileStatus::COMPLETED->value =>
            $this->resolveCompleted($audioFile),

            default =>
            new ProcessingStatusDto(0, false, false),
        };
    }

    /**
     * @param AudioFile $audioFile
     * @return ProcessingStatusDto
     */
    protected function resolveCompleted(AudioFile $audioFile): ProcessingStatusDto
    {
        $transcription = $audioFile->transcription;

        if (!$transcription) {
            return new ProcessingStatusDto(50, false, false);
        }

        return match ($transcription->status) {

            TranscriptionStatus::PROCESSING->value =>
            new ProcessingStatusDto(75, false, false),

            TranscriptionStatus::COMPLETED->value =>
            new ProcessingStatusDto(100, true, true),

            TranscriptionStatus::FAILED->value =>
            new ProcessingStatusDto(
                progress: 100,
                isFinished: true,
                isSuccessful: false,
                errorMessage: $transcription->error_message
            ),

            default =>
            new ProcessingStatusDto(50, false, false),
        };
    }
}
