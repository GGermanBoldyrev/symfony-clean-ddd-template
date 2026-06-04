<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetCurrentUser;

final readonly class GetCurrentUserQuery
{
    public function __construct(
        public string $userId,
    ) {
    }
}
