<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/TestDouble/SpyVerificationMailer.php

namespace App\Tests\Unit\Identity\Application\TestDouble;

use App\Identity\Application\Port\VerificationMailerPort;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;

final class SpyVerificationMailer implements VerificationMailerPort
{
    /** @var list<array{email: string, code: string}> */
    private array $sent = [];

    public function sendVerificationCode(Email $to, VerificationCodeValue $code): void
    {
        $this->sent[] = [
            'email' => $to->toString(),
            'code' => $code->toString(),
        ];
    }

    /**
     * @return list<array{email: string, code: string}>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    public function count(): int
    {
        return \count($this->sent);
    }
}
