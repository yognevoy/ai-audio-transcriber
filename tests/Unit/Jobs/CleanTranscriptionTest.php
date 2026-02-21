<?php

namespace Tests\Unit\Jobs;

use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Jobs\CleanTranscription;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Services\TranscriptCleanupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanTranscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_cleans_transcription_content_on_success(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        $transcription = Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'raw_content' => 'um uh like test transcription with fillers',
            'status' => TranscriptionStatus::PROCESSING->value,
            'content' => null
        ]);

        $mockService = $this->createMock(TranscriptCleanupService::class);
        $mockService->method('cleanText')
            ->with('um uh like test transcription with fillers')
            ->willReturn('Test transcription without fillers');

        $this->app->instance(TranscriptCleanupService::class, $mockService);

        $job = new CleanTranscription($audioFile->id);
        $job->handle($mockService);

        $transcription->refresh();

        $this->assertSame('Test transcription without fillers', $transcription->content);
        $this->assertSame(TranscriptionStatus::COMPLETED->value, $transcription->status);
        $this->assertNull($transcription->error_message);
    }

    public function test_job_keeps_raw_content_if_cleaning_returns_null(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        $transcription = Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'raw_content' => 'Original raw content',
            'status' => TranscriptionStatus::PROCESSING->value,
            'content' => null
        ]);

        $mockService = $this->createMock(TranscriptCleanupService::class);
        $mockService->method('cleanText')
            ->willReturn(null);

        $this->app->instance(TranscriptCleanupService::class, $mockService);

        $job = new CleanTranscription($audioFile->id);
        $job->handle($mockService);

        $transcription->refresh();

        $this->assertSame('Original raw content', $transcription->content);
        $this->assertSame(TranscriptionStatus::COMPLETED->value, $transcription->status);
    }

    public function test_job_updates_status_to_failed_on_exception(): void
    {
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::COMPLETED->value
        ]);

        $transcription = Transcription::factory()->create([
            'audio_file_id' => $audioFile->id,
            'raw_content' => 'Test content',
            'status' => TranscriptionStatus::PROCESSING->value
        ]);

        $mockService = $this->createMock(TranscriptCleanupService::class);
        $mockService->method('cleanText')
            ->willThrowException(new \Exception('Cleaning service unavailable'));

        $this->app->instance(TranscriptCleanupService::class, $mockService);

        $job = new CleanTranscription($audioFile->id);
        $job->handle($mockService);

        $transcription->refresh();

        $this->assertSame(TranscriptionStatus::FAILED->value, $transcription->status);
        $this->assertSame('Cleaning service unavailable', $transcription->error_message);
    }
}
