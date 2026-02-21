<?php

namespace Tests\Unit\Services;

use App\Dto\ProcessingStatusDto;
use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Services\ProcessingStatusResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessingStatusResolverTest extends TestCase
{
    use RefreshDatabase;

    protected ProcessingStatusResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ProcessingStatusResolver();
    }

    public function test_resolves_failed_audio_file_status(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::FAILED->value,
            'error_message' => 'Processing failed due to invalid format'
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertInstanceOf(ProcessingStatusDto::class, $status);
        $this->assertSame(0, $status->progress);
        $this->assertTrue($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertSame('Processing failed due to invalid format', $status->errorMessage);
    }

    public function test_resolves_processing_audio_file_status(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::PROCESSING->value
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(50, $status->progress);
        $this->assertFalse($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertNull($status->errorMessage);
    }

    public function test_resolves_uploaded_default_status(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::UPLOADED->value
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(0, $status->progress);
        $this->assertFalse($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertNull($status->errorMessage);
    }

    public function test_resolves_completed_with_processing_transcription(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'status' => TranscriptionStatus::PROCESSING->value
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(75, $status->progress);
        $this->assertFalse($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertNull($status->errorMessage);
    }

    public function test_resolves_completed_with_completed_transcription(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'status' => TranscriptionStatus::COMPLETED->value
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(100, $status->progress);
        $this->assertTrue($status->isFinished);
        $this->assertTrue($status->isSuccessful);
        $this->assertNull($status->errorMessage);
    }

    public function test_resolves_completed_with_failed_transcription(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'status' => TranscriptionStatus::FAILED->value,
            'error_message' => 'Text cleaning failed'
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(100, $status->progress);
        $this->assertTrue($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertSame('Text cleaning failed', $status->errorMessage);
    }

    public function test_resolves_completed_without_transcription(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        $status = $this->resolver->resolve($audioFile);

        $this->assertSame(50, $status->progress);
        $this->assertFalse($status->isFinished);
        $this->assertFalse($status->isSuccessful);
        $this->assertNull($status->errorMessage);
    }
}
