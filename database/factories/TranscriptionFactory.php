<?php

namespace Database\Factories;

use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transcription>
 */
class TranscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'audio_file_id' => AudioFile::factory(),
            'content' => null,
            'raw_content' => fake()->paragraphs(3, true),
            'status' => TranscriptionStatus::PROCESSING->value,
            'error_message' => null,
        ];
    }

    public function completed(string $content = null): static
    {
        return $this->state(fn(array $attributes) => [
            'content' => $content ?? fake()->paragraphs(3, true),
            'status' => TranscriptionStatus::COMPLETED->value,
        ]);
    }

    public function failed(string $errorMessage = 'Transcription failed'): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TranscriptionStatus::FAILED->value,
            'error_message' => $errorMessage,
        ]);
    }

    public function forAudioFile(AudioFile $audioFile): static
    {
        return $this->state(fn(array $attributes) => [
            'audio_file_id' => $audioFile->id,
        ]);
    }
}
