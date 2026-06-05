<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/InMemory/InMemoryVerificationCodeRepository.php

namespace App\Tests\Unit\Identity\Infrastructure\InMemory;

use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;

final class InMemoryVerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    /** @var array<string, VerificationCode> */
    private array $storage = [];

    private int $saveCount = 0;

    private int $upsertCount = 0;

    private int $deleteCount = 0;

    private int $deleteExpiredCount = 0;

    public function findByEmail(Email $email): ?VerificationCode
    {
        foreach ($this->storage as $code) {
            if ($code->email->equals($email)) {
                return $code;
            }
        }

        return null;
    }

    public function upsert(VerificationCode $code): void
    {
        foreach ($this->storage as $key => $existing) {
            if ($existing->email->equals($code->email)) {
                unset($this->storage[$key]);

                break;
            }
        }

        $this->storage[$code->id->toString()] = $code;
        ++$this->upsertCount;
    }

    public function save(VerificationCode $code): void
    {
        $this->storage[$code->id->toString()] = $code;
        ++$this->saveCount;
    }

    public function delete(VerificationCodeId $id): void
    {
        unset($this->storage[$id->toString()]);
        ++$this->deleteCount;
    }

    public function deleteExpired(): void
    {
        foreach ($this->storage as $key => $code) {
            if ($code->isExpired()) {
                unset($this->storage[$key]);
            }
        }

        ++$this->deleteExpiredCount;
    }

    public function count(): int
    {
        return \count($this->storage);
    }

    /**
     * @return array<string, VerificationCode>
     */
    public function all(): array
    {
        return $this->storage;
    }

    public function saveCount(): int
    {
        return $this->saveCount;
    }

    public function upsertCount(): int
    {
        return $this->upsertCount;
    }

    public function deleteCount(): int
    {
        return $this->deleteCount;
    }

    public function deleteExpiredCount(): int
    {
        return $this->deleteExpiredCount;
    }

    public function clear(): void
    {
        $this->storage = [];
        $this->saveCount = 0;
        $this->upsertCount = 0;
        $this->deleteCount = 0;
        $this->deleteExpiredCount = 0;
    }
}
