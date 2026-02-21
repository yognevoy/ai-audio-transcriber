<?php

namespace Tests\Unit\Pipelines;

use App\Jobs\CleanTranscription;
use App\Jobs\TranscribeAudioFile;
use App\Pipelines\ProcessAudioPipeline;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ProcessAudioPipelineTest extends TestCase
{
    public function test_pipeline_dispatches_job_chain(): void
    {
        Bus::fake();

        $audioFileId = 'test-audio-file-id';

        $pipeline = new ProcessAudioPipeline();
        $pipeline->handle($audioFileId);

        Bus::assertChained([
            new TranscribeAudioFile($audioFileId),
            new CleanTranscription($audioFileId),
        ]);
    }
}
