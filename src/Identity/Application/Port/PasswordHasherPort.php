<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\PlainPassword;

interface PasswordHasherPort
{
    public function hash(PlainPassword $password): HashedPassword;

    public function verify(PlainPassword $password, HashedPassword $hash): bool;
}
