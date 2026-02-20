<?php

namespace App\Dto;

readonly class ProcessingStatusDto
{
    public function __construct(
        public int     $progress,
        public bool    $isFinished,
        public bool    $isSuccessful,
        public ?string $errorMessage = null,
    )
    {
    }
}
