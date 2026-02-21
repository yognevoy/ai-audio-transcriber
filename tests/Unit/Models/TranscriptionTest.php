<?php

namespace Tests\Unit\Models;

use App\Models\AudioFile;
use App\Models\Transcription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_audio_file(): void
    {
        $audioFile = AudioFile::factory()->create();
        $transcription = Transcription::factory()->forAudioFile($audioFile)->create();

        $this->assertInstanceOf(AudioFile::class, $transcription->audioFile);
        $this->assertSame($audioFile->id, $transcription->audioFile->id);
    }

    public function test_fillable_attributes(): void
    {
        $audioFile = AudioFile::factory()->create();

        $transcription = Transcription::create([
            'audio_file_id' => $audioFile->id,
            'content' => 'Cleaned content',
            'raw_content' => 'Raw content with fillers',
            'status' => 'completed',
            'error_message' => null,
        ]);

        $this->assertSame('Cleaned content', $transcription->content);
        $this->assertSame('Raw content with fillers', $transcription->raw_content);
        $this->assertSame('completed', $transcription->status);
    }

    public function test_can_create_with_audio_file(): void
    {
        $audioFile = AudioFile::factory()->create();

        $transcription = Transcription::factory()
            ->forAudioFile($audioFile)
            ->create();

        $this->assertSame($audioFile->id, $transcription->audio_file_id);
    }
}
