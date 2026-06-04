<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;

interface VerificationCodeRepositoryInterface
{
    public function findByEmail(Email $email): ?VerificationCode;

    /**
     * INSERT or UPDATE — replaces any existing code for the given email.
     */
    public function upsert(VerificationCode $code): void;

    public function save(VerificationCode $code): void;

    public function delete(VerificationCodeId $id): void;

    public function deleteExpired(): void;
}
