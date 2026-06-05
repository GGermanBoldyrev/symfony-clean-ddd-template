<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;

interface VerificationCodeGeneratorPort
{
    public function generate(): VerificationCodeValue;
}
