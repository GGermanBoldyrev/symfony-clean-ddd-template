<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidCodeException extends DomainException
{
    public static function mismatch(): self
    {
        return new self('The submitted verification code is incorrect.');
    }
}
