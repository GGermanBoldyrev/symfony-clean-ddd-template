<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class MaxAttemptsExceededException extends DomainException
{
    public static function exceeded(): self
    {
        return new self('Maximum number of verification attempts has been exceeded.');
    }
}
