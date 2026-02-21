<?php

namespace Database\Factories;

use App\Enums\AudioFileStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AudioFile>
 */
class AudioFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'filename' => fake()->word() . '.mp3',
            'path' => 'audio_files/' . fake()->uuid() . '.mp3',
            'size' => fake()->numberBetween(100000, 10000000),
            'mime_type' => 'audio/mpeg',
            'duration' => fake()->randomFloat(2, 10, 300),
            'status' => AudioFileStatus::UPLOADED->value,
            'error_message' => null,
            'uploaded_at' => now(),
            'processed_at' => null,
            'metadata' => null,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AudioFileStatus::PROCESSING->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AudioFileStatus::COMPLETED->value,
            'processed_at' => now(),
        ]);
    }

    public function failed(string $errorMessage = 'Processing failed'): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AudioFileStatus::FAILED->value,
            'error_message' => $errorMessage,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
