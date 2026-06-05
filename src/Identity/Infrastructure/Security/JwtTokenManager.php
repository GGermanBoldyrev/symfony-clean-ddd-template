<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use App\Identity\Domain\ValueObject\User\UserId;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Psr\Clock\ClockInterface;
use Throwable;

final class JwtTokenManager implements TokenManagerPort
{
    private const string CLAIM_TYPE = 'type';
    private const string TYPE_ACCESS = 'access';
    private const string TYPE_REFRESH = 'refresh';

    private readonly Configuration $config;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly string $privateKeyPath,
        private readonly string $publicKeyPath,
        private readonly string $passphrase,
        private readonly string $issuer,
        private readonly int $accessTokenTtlSeconds,
        private readonly int $refreshTokenTtlSeconds,
    ) {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($this->privateKeyPath, $this->passphrase),
            InMemory::file($this->publicKeyPath),
        );
    }

    public function issueAccessToken(UserId $userId): AccessToken
    {
        return AccessToken::fromString(
            $this->buildToken($userId, self::TYPE_ACCESS, $this->accessTokenTtlSeconds),
        );
    }

    public function issueRefreshToken(UserId $userId): RefreshToken
    {
        return RefreshToken::fromString(
            $this->buildToken($userId, self::TYPE_REFRESH, $this->refreshTokenTtlSeconds),
        );
    }

    public function parseAccessToken(AccessToken $token): ?UserId
    {
        return $this->parseToken($token->toString(), self::TYPE_ACCESS);
    }

    public function parseRefreshToken(RefreshToken $token): ?UserId
    {
        return $this->parseToken($token->toString(), self::TYPE_REFRESH);
    }

    public function accessTokenTtl(): int
    {
        return $this->accessTokenTtlSeconds;
    }

    public function refreshTokenTtl(): int
    {
        return $this->refreshTokenTtlSeconds;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildToken(UserId $userId, string $type, int $ttlSeconds): string
    {
        $now = DateTimeImmutable::createFromInterface($this->clock->now());

        return $this->config
            ->builder()
            ->issuedBy($this->issuer)
            ->relatedTo($userId->toString())
            ->issuedAt($now)
            ->expiresAt($now->modify(\sprintf('+%d seconds', $ttlSeconds)))
            ->withClaim(self::CLAIM_TYPE, $type)
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }

    private function parseToken(string $raw, string $expectedType): ?UserId
    {
        try {
            $token = $this->config->parser()->parse($raw);

            if (!$token instanceof Plain) {
                return null;
            }

            $this->config->validator()->assert($token, ...[
                new IssuedBy($this->issuer),
                new SignedWith($this->config->signer(), $this->config->verificationKey()),
                new LooseValidAt($this->clock),
            ]);

            if ($token->claims()->get(self::CLAIM_TYPE) !== $expectedType) {
                return null;
            }

            $subject = $token->claims()->get('sub');

            if (!\is_string($subject)) {
                return null;
            }

            return UserId::fromString($subject);
        } catch (Throwable) {
            return null;
        }
    }
}
