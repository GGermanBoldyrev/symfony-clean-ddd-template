<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Security/JwtTokenManagerTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Security;

use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Infrastructure\Security\JwtTokenManager;
use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\Clock\FrozenClock;
use LogicException;
use OpenSSLAsymmetricKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtTokenManagerTest extends TestCase
{
    private ?string $privateKeyPath = null;

    private ?string $publicKeyPath = null;

    private ?string $tempDirectory = null;

    protected function setUp(): void
    {
        $tempDirectory = sys_get_temp_dir() . '/jwt-token-manager-test-' . bin2hex(random_bytes(6));
        self::assertTrue(mkdir($tempDirectory));
        $this->tempDirectory = $tempDirectory;

        $key = openssl_pkey_new([
            'private_key_type' => \OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        self::assertInstanceOf(OpenSSLAsymmetricKey::class, $key);

        $privateKey = '';
        self::assertTrue(openssl_pkey_export($key, $privateKey));

        $details = openssl_pkey_get_details($key);
        self::assertIsArray($details);
        self::assertArrayHasKey('key', $details);
        self::assertIsString($details['key']);

        $privateKeyPath = $tempDirectory . '/private.pem';
        $publicKeyPath = $tempDirectory . '/public.pem';
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath = $publicKeyPath;

        self::assertNotFalse(file_put_contents($privateKeyPath, $privateKey));
        self::assertNotFalse(file_put_contents($publicKeyPath, $details['key']));
    }

    protected function tearDown(): void
    {
        if ($this->privateKeyPath !== null && is_file($this->privateKeyPath)) {
            unlink($this->privateKeyPath);
        }

        if ($this->publicKeyPath !== null && is_file($this->publicKeyPath)) {
            unlink($this->publicKeyPath);
        }

        if ($this->tempDirectory !== null && is_dir($this->tempDirectory)) {
            rmdir($this->tempDirectory);
        }
    }

    #[Test]
    public function itIssuesAndParsesAccessToken(): void
    {
        $manager = $this->manager(new FrozenClock(new DateTimeImmutable('2024-01-01T12:00:00+00:00')));
        $userId = UserId::generate();

        $token = $manager->issueAccessToken($userId);
        $parsed = $manager->parseAccessToken($token);

        self::assertNotNull($parsed);
        self::assertTrue($userId->equals($parsed));
        self::assertNull($manager->parseRefreshToken(RefreshToken::fromString($token->toString())));
    }

    #[Test]
    public function itIssuesAndParsesRefreshToken(): void
    {
        $manager = $this->manager(new FrozenClock(new DateTimeImmutable('2024-01-01T12:00:00+00:00')));
        $userId = UserId::generate();

        $token = $manager->issueRefreshToken($userId);
        $parsed = $manager->parseRefreshToken($token);

        self::assertNotNull($parsed);
        self::assertTrue($userId->equals($parsed));
        self::assertNull($manager->parseAccessToken(AccessToken::fromString($token->toString())));
    }

    #[Test]
    public function itRejectsEmptyMalformedAndExpiredTokens(): void
    {
        $clock = new FrozenClock(new DateTimeImmutable('2024-01-01T12:00:00+00:00'));
        $manager = $this->manager($clock, accessTokenTtlSeconds: 1);
        $token = $manager->issueAccessToken(UserId::generate());

        self::assertNull($manager->parseAccessToken(AccessToken::fromString('')));
        self::assertNull($manager->parseAccessToken(AccessToken::fromString('not-a-jwt')));

        $clock->adjustTime('+2 seconds');

        self::assertNull($manager->parseAccessToken($token));
    }

    #[Test]
    public function itExposesConfiguredTtls(): void
    {
        $manager = $this->manager(
            new FrozenClock(new DateTimeImmutable('2024-01-01T12:00:00+00:00')),
            accessTokenTtlSeconds: 123,
            refreshTokenTtlSeconds: 456,
        );

        self::assertSame(123, $manager->accessTokenTtl());
        self::assertSame(456, $manager->refreshTokenTtl());
    }

    #[Test]
    public function itRejectsEmptyConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new JwtTokenManager(
            new FrozenClock(new DateTimeImmutable('2024-01-01T12:00:00+00:00')),
            '',
            $this->publicKeyPath(),
            '',
            'kinodom',
            60,
            3600,
        );
    }

    private function manager(
        FrozenClock $clock,
        int $accessTokenTtlSeconds = 60,
        int $refreshTokenTtlSeconds = 3600,
    ): JwtTokenManager {
        return new JwtTokenManager(
            $clock,
            $this->privateKeyPath(),
            $this->publicKeyPath(),
            '',
            'kinodom',
            $accessTokenTtlSeconds,
            $refreshTokenTtlSeconds,
        );
    }

    private function privateKeyPath(): string
    {
        return $this->privateKeyPath ?? throw new LogicException('Private key path is not initialized.');
    }

    private function publicKeyPath(): string
    {
        return $this->publicKeyPath ?? throw new LogicException('Public key path is not initialized.');
    }
}
