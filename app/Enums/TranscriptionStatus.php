<?php

namespace App\Enums;

enum TranscriptionStatus: string
{
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}