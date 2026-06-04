<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;
use App\Identity\Domain\ValueObject\User\Email;

final class UserAlreadyVerifiedException extends DomainException
{
    public function __construct(Email $email)
    {
        parent::__construct(sprintf('User "%s" is already verified.', $email->toString()));
    }
}
