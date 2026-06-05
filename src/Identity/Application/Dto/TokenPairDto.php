<?php

declare(strict_types=1);

namespace App\Identity\Application\Dto;

use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;

final readonly class TokenPairDto
{
    public function __construct(
        public AccessToken $accessToken,
        public RefreshToken $refreshToken,
    ) {
    }
}
