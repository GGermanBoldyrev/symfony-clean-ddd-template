<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class DataPolicyNotAcceptedException extends DomainException
{
    public static function notAccepted(): self
    {
        return new self('Registration requires explicit acceptance of the data-processing policy.');
    }
}
