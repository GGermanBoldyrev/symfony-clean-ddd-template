<?php

declare(strict_types=1);

// tests/Unit/Identity/Application/TestDouble/FakeTokenManager.php

namespace App\Tests\Unit\Identity\Application\TestDouble;

use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use App\Identity\Domain\ValueObject\User\UserId;

final class FakeTokenManager implements TokenManagerPort
{
    private ?UserId $parsedAccessTokenUserId = null;

    private ?UserId $parsedRefreshTokenUserId = null;

    private int $accessIssueCount = 0;

    private int $refreshIssueCount = 0;

    public function issueAccessToken(UserId $userId): AccessToken
    {
        ++$this->accessIssueCount;

        return AccessToken::fromString('access:' . $userId->toString());
    }

    public function issueRefreshToken(UserId $userId): RefreshToken
    {
        ++$this->refreshIssueCount;

        return RefreshToken::fromString('refresh:' . $userId->toString());
    }

    public function parseAccessToken(AccessToken $token): ?UserId
    {
        return $this->parsedAccessTokenUserId;
    }

    public function parseRefreshToken(RefreshToken $token): ?UserId
    {
        return $this->parsedRefreshTokenUserId;
    }

    public function accessTokenTtl(): int
    {
        return 300;
    }

    public function refreshTokenTtl(): int
    {
        return 3600;
    }

    public function parseAccessTokenAs(?UserId $userId): void
    {
        $this->parsedAccessTokenUserId = $userId;
    }

    public function parseRefreshTokenAs(?UserId $userId): void
    {
        $this->parsedRefreshTokenUserId = $userId;
    }

    public function accessIssueCount(): int
    {
        return $this->accessIssueCount;
    }

    public function refreshIssueCount(): int
    {
        return $this->refreshIssueCount;
    }
}
