<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;
use App\Identity\Domain\ValueObject\User\Email;

final class UserAlreadyVerifiedException extends DomainException
{
    private const string ERROR_CODE = 'user.already_verified';

    public function __construct(Email $email)
    {
        parent::__construct(\sprintf('User "%s" is already verified.', $email->toString()));
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
