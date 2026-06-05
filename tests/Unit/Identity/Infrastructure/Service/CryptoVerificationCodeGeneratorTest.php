<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Service/CryptoVerificationCodeGeneratorTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Service;

use App\Identity\Infrastructure\Service\CryptoVerificationCodeGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CryptoVerificationCodeGeneratorTest extends TestCase
{
    #[Test]
    public function itGeneratesSixDigitVerificationCode(): void
    {
        $code = (new CryptoVerificationCodeGenerator())->generate();

        self::assertMatchesRegularExpression('/^\d{6}$/', $code->toString());
    }
}
