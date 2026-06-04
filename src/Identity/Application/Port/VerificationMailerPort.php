<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;

interface VerificationMailerPort
{
    public function sendVerificationCode(Email $to, VerificationCodeValue $code): void;
}
