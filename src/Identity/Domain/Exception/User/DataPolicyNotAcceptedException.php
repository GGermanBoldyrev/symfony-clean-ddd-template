<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception\User;

use App\Identity\Domain\Exception\DomainException;

final class DataPolicyNotAcceptedException extends DomainException
{
    private const string ERROR_CODE = 'user.data_policy_not_accepted';

    public static function notAccepted(): self
    {
        return new self('Registration requires explicit acceptance of the data-processing policy.');
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
