<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\VerifyCode;

final readonly class VerifyCodeCommand
{
    public function __construct(
        public string $email,
        public string $code,
    ) {
    }
}
