<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

use App\Identity\Domain\ValueObject\AccessToken;
use App\Identity\Domain\ValueObject\RefreshToken;
use App\Identity\Domain\ValueObject\User\UserId;

interface TokenManagerPort
{
    public function issueAccessToken(UserId $userId): AccessToken;

    public function issueRefreshToken(UserId $userId): RefreshToken;

    public function parseAccessToken(AccessToken $token): ?UserId;

    public function parseRefreshToken(RefreshToken $token): ?UserId;

    public function accessTokenTtl(): int;

    public function refreshTokenTtl(): int;
}
