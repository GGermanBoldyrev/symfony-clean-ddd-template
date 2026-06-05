<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserDoctrineEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 320, unique: true)]
    public string $email;

    #[ORM\Column(name: 'password_hash', type: 'string', length: 255)]
    public string $passwordHash;

    #[ORM\Column(name: 'verified_at', type: 'datetimetz_immutable', nullable: true)]
    public ?DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(name: 'data_policy_accepted_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $dataPolicyAcceptedAt;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetimetz_immutable')]
    public DateTimeImmutable $updatedAt;
}

