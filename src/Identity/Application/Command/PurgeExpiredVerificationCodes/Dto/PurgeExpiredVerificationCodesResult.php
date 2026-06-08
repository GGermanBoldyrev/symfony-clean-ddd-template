<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\PurgeExpiredVerificationCodes\Dto;

final readonly class PurgeExpiredVerificationCodesResult
{
    public function __construct(
        public readonly int $deletedCount,
    ) {
    }
}
