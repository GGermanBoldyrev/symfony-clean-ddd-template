<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ResendVerificationCode;

final readonly class ResendVerificationCodeCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
