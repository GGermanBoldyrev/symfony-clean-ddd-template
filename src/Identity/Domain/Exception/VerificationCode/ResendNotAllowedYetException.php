<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;
use DateTimeImmutable;


final class ResendNotAllowedYetException extends DomainException
{
    public static function before(DateTimeImmutable $resendAfter): self
    {
        return new self(
            sprintf(
                'Resend is not allowed until %s.',
                $resendAfter->format('Y-m-d H:i:s'),
            ),
        );
    }
}
