<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class CodeExpiredException extends DomainException
{
    public static function expired(): self
    {
        return new self('The verification code has expired.');
    }
}
