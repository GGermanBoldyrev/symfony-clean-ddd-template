<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RefreshToken;

use App\Identity\Application\Dto\TokenPairDto;
use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\RefreshToken;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler]
final readonly class RefreshTokenCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenManagerPort $tokenManager,
    ) {
    }

    public function __invoke(RefreshTokenCommand $command): TokenPairDto
    {
        $token = RefreshToken::fromString($command->refreshToken);
        $userId = $this->tokenManager->parseRefreshToken($token);

        if ($userId === null) {
            throw new AuthenticationException('Refresh token is invalid or expired.');
        }

        $user = $this->users->findById($userId);

        if ($user === null || !$user->isVerified()) {
            throw new AuthenticationException('Refresh token is invalid or expired.');
        }

        return new TokenPairDto(
            accessToken: $this->tokenManager->issueAccessToken($user->id),
            refreshToken: $this->tokenManager->issueRefreshToken($user->id),
        );
    }
}
