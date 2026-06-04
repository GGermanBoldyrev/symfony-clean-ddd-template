<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidResendAfterException extends DomainException
{
    public static function notInFuture(): self
    {
        return new self('Resend-after date must be in the future.');
    }

    public static function afterExpiration(): self
    {
        return new self('Resend-after date must be before the expiration date.');
    }
}
