<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class CodeExpiredException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.expired';

    public static function expired(): self
    {
        return new self('The verification code has expired.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
