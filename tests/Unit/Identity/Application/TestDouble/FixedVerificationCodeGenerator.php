<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/TestDouble/FixedVerificationCodeGenerator.php

namespace App\Tests\Unit\Identity\Application\TestDouble;

use App\Identity\Application\Port\VerificationCodeGeneratorPort;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;

final class FixedVerificationCodeGenerator implements VerificationCodeGeneratorPort
{
    /** @var non-empty-list<VerificationCodeValue> */
    private array $codes;

    private int $index = 0;

    /**
     * @param non-empty-list<string> $codes
     */
    public function __construct(array $codes = ['123456'])
    {
        $this->codes = array_map(
            static fn (string $code): VerificationCodeValue => VerificationCodeValue::fromString($code),
            $codes,
        );
    }

    public function generate(): VerificationCodeValue
    {
        $code = $this->codes[min($this->index, \count($this->codes) - 1)];
        ++$this->index;

        return $code;
    }

    public function generatedCount(): int
    {
        return $this->index;
    }
}
