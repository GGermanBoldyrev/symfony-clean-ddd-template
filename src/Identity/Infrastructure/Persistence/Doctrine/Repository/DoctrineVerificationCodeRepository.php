<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Persistence\Doctrine\Repository;

use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\AttemptCount;
use App\Identity\Domain\ValueObject\VerificationCode\ExpiresAt;
use App\Identity\Domain\ValueObject\VerificationCode\MaxAttempts;
use App\Identity\Domain\ValueObject\VerificationCode\ResendAfter;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use App\Identity\Infrastructure\Persistence\Doctrine\Entity\VerificationCodeDoctrineEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineVerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function findByEmail(Email $email): ?VerificationCode
    {
        $entity = $this->em
            ->getRepository(VerificationCodeDoctrineEntity::class)
            ->findOneBy(['email' => $email->toString()]);

        if ($entity === null) {
            return null;
        }

        return $this->toDomain($entity);
    }

    public function upsert(VerificationCode $code): void
    {
        $existing = $this->em
            ->getRepository(VerificationCodeDoctrineEntity::class)
            ->findOneBy(['email' => $code->email->toString()]);

        if ($existing !== null) {
            $this->em->remove($existing);
            $this->em->flush();
        }

        $entity = $this->toEntity($code);
        $this->em->persist($entity);
    }

    public function save(VerificationCode $code): void
    {
        $entity = $this->em->find(VerificationCodeDoctrineEntity::class, $code->id->toString());

        if ($entity === null) {
            $entity = $this->toEntity($code);
        } else {
            $entity->attempts = $code->attempts->toInt();
            $entity->updatedAt = $code->updatedAt;
        }

        $this->em->persist($entity);
    }

    public function delete(VerificationCodeId $id): void
    {
        $entity = $this->em->find(VerificationCodeDoctrineEntity::class, $id->toString());

        if ($entity !== null) {
            $this->em->remove($entity);
        }
    }

    public function deleteExpired(): int
    {
        $result = $this->em->createQueryBuilder()
            ->delete(VerificationCodeDoctrineEntity::class, 'v')
            ->where('v.expiresAt < :now')
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->execute();

        return \is_scalar($result) ? (int) $result : 0;
    }

    private function toDomain(VerificationCodeDoctrineEntity $entity): VerificationCode
    {
        return new VerificationCode(
            id: VerificationCodeId::fromString($entity->id),
            email: Email::fromString($entity->email),
            code: VerificationCodeValue::fromString($entity->code),
            attempts: AttemptCount::fromInt($entity->attempts),
            maxAttempts: MaxAttempts::fromInt($entity->maxAttempts),
            expiresAt: ExpiresAt::fromDateTimeImmutable($entity->expiresAt),
            resendAfter: ResendAfter::fromDateTimeImmutable($entity->resendAfter),
            createdAt: $entity->createdAt,
        );
    }

    private function toEntity(VerificationCode $code): VerificationCodeDoctrineEntity
    {
        $entity = new VerificationCodeDoctrineEntity();
        $entity->id = $code->id->toString();
        $entity->email = $code->email->toString();
        $entity->code = $code->code->toString();
        $entity->attempts = $code->attempts->toInt();
        $entity->maxAttempts = $code->maxAttempts->toInt();
        $entity->expiresAt = $code->expiresAt->toDateTimeImmutable();
        $entity->resendAfter = $code->resendAfter->toDateTimeImmutable();
        $entity->createdAt = $code->createdAt;
        $entity->updatedAt = $code->updatedAt;

        return $entity;
    }
}
