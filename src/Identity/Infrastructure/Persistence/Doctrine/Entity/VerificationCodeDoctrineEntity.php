<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'verification_codes')]
#[ORM\UniqueConstraint(name: 'uq_verification_codes_email', columns: ['email'])]
#[ORM\Index(name: 'idx_verification_codes_expires_at', columns: ['expires_at'])]
class VerificationCodeDoctrineEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 320)]
    public string $email;

    #[ORM\Column(type: 'string', length: 6)]
    public string $code;

    #[ORM\Column(type: 'smallint')]
    public int $attempts;

    #[ORM\Column(name: 'max_attempts', type: 'smallint')]
    public int $maxAttempts;

    #[ORM\Column(name: 'expires_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'resend_after', type: 'datetimetz_immutable')]
    public DateTimeImmutable $resendAfter;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $updatedAt;
}
