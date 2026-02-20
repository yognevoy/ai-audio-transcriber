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
            new ProcessingStatusDto(0, 'failed'),

            AudioFileStatus::PROCESSING->value =>
            new ProcessingStatusDto(50, 'transcribing'),

            AudioFileStatus::COMPLETED->value =>
            $this->resolveCompleted($audioFile),

            default =>
            new ProcessingStatusDto(0, 'pending'),
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
            return new ProcessingStatusDto(50, 'transcribing');
        }

        return match ($transcription->status) {
            TranscriptionStatus::PROCESSING->value =>
            new ProcessingStatusDto(75, 'cleaning'),

            TranscriptionStatus::COMPLETED->value =>
            new ProcessingStatusDto(100, 'completed'),

            TranscriptionStatus::FAILED->value =>
            new ProcessingStatusDto(75, 'cleaning_failed'),

            default =>
            new ProcessingStatusDto(50, 'transcribing'),
        };
    }
}
