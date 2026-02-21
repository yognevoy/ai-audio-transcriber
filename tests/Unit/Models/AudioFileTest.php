<?php

namespace Tests\Unit\Models;

use App\Enums\AudioFileStatus;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudioFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $audioFile = AudioFile::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $audioFile->user);
        $this->assertSame($user->id, $audioFile->user->id);
    }

    public function test_has_one_transcription(): void
    {
        $audioFile = AudioFile::factory()->create();
        $transcription = Transcription::factory()->forAudioFile($audioFile)->create();

        $this->assertInstanceOf(Transcription::class, $audioFile->transcription);
        $this->assertSame($transcription->id, $audioFile->transcription->id);
    }

    public function test_size_formatted_attribute(): void
    {
        $audioFile = AudioFile::factory()->create(['size' => 1024]);
        $this->assertSame('1 KB', $audioFile->size_formatted);

        $audioFile = AudioFile::factory()->create(['size' => 1048576]);
        $this->assertSame('1 MB', $audioFile->size_formatted);

        $audioFile = AudioFile::factory()->create(['size' => 512]);
        $this->assertSame('512 B', $audioFile->size_formatted);
    }

    public function test_duration_is_cast_to_decimal(): void
    {
        $audioFile = AudioFile::factory()->create(['duration' => 123.456]);

        $this->assertSame('123.46', $audioFile->duration);
    }

    public function test_uploaded_at_is_cast_to_datetime(): void
    {
        $audioFile = AudioFile::factory()->create();

        $this->assertInstanceOf(\Carbon\Carbon::class, $audioFile->uploaded_at);
    }

    public function test_metadata_is_cast_to_array(): void
    {
        $audioFile = AudioFile::factory()->create(['metadata' => ['key' => 'value']]);

        $this->assertIsArray($audioFile->metadata);
        $this->assertSame(['key' => 'value'], $audioFile->metadata);
    }

    public function test_fillable_attributes(): void
    {
        $user = User::factory()->create();

        $audioFile = AudioFile::create([
            'user_id' => $user->id,
            'filename' => 'test.mp3',
            'path' => 'audio_files/test.mp3',
            'size' => 1024,
            'mime_type' => 'audio/mpeg',
            'duration' => 120.5,
            'status' => AudioFileStatus::UPLOADED->value,
            'error_message' => null,
            'uploaded_at' => now(),
            'processed_at' => null,
            'metadata' => ['sample_rate' => 44100],
        ]);

        $this->assertSame('test.mp3', $audioFile->filename);
        $this->assertSame(1024, $audioFile->size);
        $this->assertSame(['sample_rate' => 44100], $audioFile->metadata);
    }

    public function test_can_create_with_transcription(): void
    {
        $user = User::factory()->create();

        $audioFile = AudioFile::factory()
            ->forUser($user)
            ->has(Transcription::factory()->count(1), 'transcription')
            ->create();

        $this->assertNotNull($audioFile->transcription);
    }
}
