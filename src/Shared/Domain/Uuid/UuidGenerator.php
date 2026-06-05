<?php

declare(strict_types=1);

namespace App\Shared\Domain\Uuid;

final class UuidGenerator
{
    /**
     * Generates a new UUID v7 string.
     *
     * Example output: 018f4e3a-2b7c-7d4e-9a1b-3c5d7e9f1a2b
     */
    public static function generate(): string
    {
        $timestampMs = (int) (microtime(true) * 1000);

        $timeHex = str_pad(dechex($timestampMs), 12, '0', \STR_PAD_LEFT);

        $random = random_bytes(10);

        $versionByte = (\ord($random[0]) & 0x0F) | 0x70;
        $variantByte = (\ord($random[2]) & 0x3F) | 0x80;

        return \sprintf(
            '%s-%s-%s%s-%s%s-%s',
            substr($timeHex, 0, 8),
            substr($timeHex, 8, 4),
            dechex($versionByte),
            bin2hex($random[1]),
            dechex($variantByte),
            bin2hex($random[3]),
            bin2hex(substr($random, 4, 6)),
        );
    }

    /**
     * Validates any UUID string (v1–v8) per RFC 9562 format rules.
     */
    public static function isValid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}
