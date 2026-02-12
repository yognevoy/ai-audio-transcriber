<?php

namespace App\Pipelines;

use App\Jobs\CleanTranscription;
use App\Jobs\TranscribeAudioFile;
use Illuminate\Support\Facades\Bus;

class ProcessAudioPipeline
{
    public function handle(string $audioFileId): void
    {
        Bus::chain([
            new TranscribeAudioFile($audioFileId),
            new CleanTranscription($audioFileId),
        ])->dispatch();
    }
}
