<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\ValueObject\User;

use App\Identity\Domain\Exception\User\DataPolicyNotAcceptedException;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DataPolicyAcceptedAtTest extends TestCase
{
    #[Test]
    public function itAcceptsWhenPolicyIsAccepted(): void
    {
        $before = new DateTimeImmutable();
        $vo = DataPolicyAcceptedAt::accept(true);

        self::assertGreaterThanOrEqual($before, $vo->toDateTimeImmutable());
    }

    #[Test]
    public function itThrowsWhenPolicyIsNotAccepted(): void
    {
        $this->expectException(DataPolicyNotAcceptedException::class);

        DataPolicyAcceptedAt::accept(false);
    }

    #[Test]
    public function itCanBeReconstructedFromDateTimeImmutable(): void
    {
        $now = new DateTimeImmutable('2024-01-01 12:00:00');
        $vo = DataPolicyAcceptedAt::fromDateTimeImmutable($now);

        self::assertSame($now, $vo->toDateTimeImmutable());
    }
}
