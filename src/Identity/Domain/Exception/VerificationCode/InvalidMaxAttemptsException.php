<?php


declare(strict_types=1);

namespace App\Identity\Domain\Exception\VerificationCode;

use App\Identity\Domain\Exception\DomainException;

final class InvalidMaxAttemptsException extends DomainException
{
    public static function notPositive(int $value): self
    {
        return new self(
            sprintf('Max attempts must be a positive integer, got %d.', $value),
        );
    }
}
