<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine\Repository;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\HashedPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\User\VerifiedAt;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\UserDoctrineEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function findById(UserId $id): ?User
    {
        $entity = $this->em->find(UserDoctrineEntity::class, $id->toString());

        if ($entity === null) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function findByEmail(Email $email): ?User
    {
        $entity = $this->em
            ->getRepository(UserDoctrineEntity::class)
            ->findOneBy(['email' => $email->toString()]);

        if ($entity === null) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function save(User $user): void
    {
        $existing = $this->em->find(UserDoctrineEntity::class, $user->id->toString());

        if ($existing === null) {
            $entity = new UserDoctrineEntity();
            $entity->id = $user->id->toString();
            $entity->createdAt = $user->createdAt;
        } else {
            $entity = $existing;
        }

        $entity->email = $user->email->toString();
        $entity->passwordHash = $user->passwordHash->toString();
        $entity->verifiedAt = $user->verifiedAt?->toDateTimeImmutable();
        $entity->dataPolicyAcceptedAt = $user->dataPolicyAcceptedAt->toDateTimeImmutable();
        $entity->updatedAt = $user->updatedAt;

        $this->em->persist($entity);
    }

    private function toDomain(UserDoctrineEntity $entity): User
    {
        return new User(
            id: UserId::fromString($entity->id),
            email: Email::fromString($entity->email),
            passwordHash: HashedPassword::fromRawHash($entity->passwordHash),
            dataPolicyAcceptedAt: DataPolicyAcceptedAt::fromDateTimeImmutable($entity->dataPolicyAcceptedAt),
            createdAt: $entity->createdAt,
            verifiedAt: $entity->verifiedAt !== null
                ? VerifiedAt::fromDateTimeImmutable($entity->verifiedAt)
                : null,
        );
    }
}
