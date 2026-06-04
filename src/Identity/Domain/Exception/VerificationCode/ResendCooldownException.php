<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;
use DateTimeImmutable;

final class ResendCooldownException extends DomainException
{
    public function __construct(DateTimeImmutable $resendAfter)
    {
        parent::__construct(
            sprintf(
                'Code resend is not available until %s.',
                $resendAfter->format('Y-m-d H:i:s'),
            ),
        );
    }
}
