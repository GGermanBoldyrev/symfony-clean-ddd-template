<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class UserNotFoundException extends DomainException
{
    private const string ERROR_CODE = 'user.not_found';

    public static function withId(string $id): self
    {
        return new self(sprintf('User with ID "%s" not found.', $id));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" not found.', $email));
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
