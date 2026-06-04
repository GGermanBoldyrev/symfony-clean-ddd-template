<?php

declare(strict_types=1);

namespace App\Identity\Application\Dto;

final readonly class CurrentUserDto
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $isVerified,
        // ISO 8601 string or null if not verified yet.
        public ?string $verifiedAt,
        // ISO 8601 string — when the data policy was accepted.
        public string $dataPolicyAcceptedAt,
    ) {
    }
}
