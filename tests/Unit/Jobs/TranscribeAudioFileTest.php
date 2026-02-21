<?php

namespace Tests\Unit\Jobs;

use App\Enums\AudioFileStatus;
use App\Enums\TranscriptionStatus;
use App\Jobs\TranscribeAudioFile;
use App\Models\AudioFile;
use App\Models\Transcription;
use App\Services\AudioTranscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranscribeAudioFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_transcription_on_success(): void
    {
        Storage::fake('public');
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::UPLOADED->value,
            'path' => 'audio_files/test.mp3'
        ]);

        Storage::disk('public')->put('audio_files/test.mp3', 'fake audio content');

        $mockService = $this->createMock(AudioTranscriptionService::class);
        $mockService->method('transcribe')
            ->willReturn(['text' => 'This is the transcribed text']);

        $this->app->instance(AudioTranscriptionService::class, $mockService);

        $job = new TranscribeAudioFile($audioFile->id);
        $job->handle($mockService);

        $audioFile->refresh();

        $this->assertSame(AudioFileStatus::COMPLETED->value, $audioFile->status);
        $this->assertNotNull($audioFile->processed_at);

        $this->assertDatabaseHas('transcriptions', [
            'audio_file_id' => $audioFile->id,
            'raw_content' => 'This is the transcribed text',
            'status' => TranscriptionStatus::PROCESSING->value,
        ]);
    }

    public function test_job_updates_status_to_failed_on_service_failure(): void
    {
        Storage::fake('public');
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::UPLOADED->value,
            'path' => 'audio_files/test.mp3'
        ]);

        Storage::disk('public')->put('audio_files/test.mp3', 'fake audio content');

        $mockService = $this->createMock(AudioTranscriptionService::class);
        $mockService->method('transcribe')
            ->willReturn(null);

        $this->app->instance(AudioTranscriptionService::class, $mockService);

        $job = new TranscribeAudioFile($audioFile->id);
        $job->handle($mockService);

        $audioFile->refresh();

        $this->assertSame(AudioFileStatus::FAILED->value, $audioFile->status);
        $this->assertSame('Transcription failed', $audioFile->error_message);
    }

    public function test_job_updates_status_to_failed_on_exception(): void
    {
        Storage::fake('public');
        $audioFile = AudioFile::factory()->create([
            'status' => AudioFileStatus::UPLOADED->value,
            'path' => 'audio_files/test.mp3'
        ]);

        Storage::disk('public')->put('audio_files/test.mp3', 'fake audio content');

        $mockService = $this->createMock(AudioTranscriptionService::class);
        $mockService->method('transcribe')
            ->willThrowException(new \Exception('API connection error'));

        $this->app->instance(AudioTranscriptionService::class, $mockService);

        $job = new TranscribeAudioFile($audioFile->id);
        $job->handle($mockService);

        $audioFile->refresh();

        $this->assertSame(AudioFileStatus::FAILED->value, $audioFile->status);
        $this->assertSame('API connection error', $audioFile->error_message);
    }
}
