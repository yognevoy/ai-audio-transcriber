<?php

namespace App\Dto;

readonly class ProcessingStatusDto
{
    public function __construct(
        public int    $progress,
        public string $stage,
    ) {}
}
