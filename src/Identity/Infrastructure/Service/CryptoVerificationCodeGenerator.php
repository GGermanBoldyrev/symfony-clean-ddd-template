<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Service;

use App\Identity\Domain\Service\VerificationCodeGeneratorInterface;
use App\Identity\Domain\ValueObject\VerificationCodeValue;
use App\Support\Crypto\CryptoCodeGenerator;

final class CryptoVerificationCodeGenerator implements VerificationCodeGeneratorInterface
{
    public function generate(): VerificationCodeValue
    {
        return VerificationCodeValue::fromString(
            CryptoCodeGenerator::numericCode(VerificationCodeValue::length()),
        );
    }
}
