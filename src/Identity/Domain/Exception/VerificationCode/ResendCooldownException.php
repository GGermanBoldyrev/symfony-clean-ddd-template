<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;
use DateTimeImmutable;

final class ResendCooldownException extends DomainException
{
    private const string ERROR_CODE = 'verification_code.resend_cooldown';

    public static function before(DateTimeImmutable $resendAfter): self
    {
        return new self(
            \sprintf(
                'Code resend is not available until %s.',
                $resendAfter->format('Y-m-d H:i:s'),
            ),
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
