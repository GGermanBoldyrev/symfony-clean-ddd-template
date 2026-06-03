<?php

declare(strict_types=1);

namespace App\Identity\Domain\Service;

use App\Identity\Domain\ValueObject\VerificationCodeValue;

interface VerificationCodeGeneratorInterface
{
    public function generate(): VerificationCodeValue;
}
