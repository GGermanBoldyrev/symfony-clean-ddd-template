<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidExpiresAtException extends DomainException
{
    public static function notInFuture(): self
    {
        return new self('Expiration date must be in the future.');
    }
}
