<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Service;

use App\Identity\Application\Port\VerificationCodeGeneratorPort;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;

final class CryptoVerificationCodeGenerator implements VerificationCodeGeneratorPort
{
    public function generate(): VerificationCodeValue
    {
        $code = str_pad(
            string: (string) random_int(0, 999_999),
            length: 6,
            pad_string: '0',
            pad_type: \STR_PAD_LEFT,
        );

        return VerificationCodeValue::fromString($code);
    }
}
