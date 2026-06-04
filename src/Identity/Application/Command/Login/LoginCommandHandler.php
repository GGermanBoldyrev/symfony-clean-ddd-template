<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\Login;

use App\Identity\Application\Dto\TokenPairDto;
use App\Identity\Application\Port\PasswordHasherPort;
use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Domain\Exception\DomainException;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[AsMessageHandler]
final readonly class LoginCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherPort $hasher,
        private TokenManagerPort $tokenManager,
    ) {
    }

    public function __invoke(LoginCommand $command): TokenPairDto
    {
        try {
            $email = Email::fromString($command->email);
            $password = PlainPassword::fromString($command->password);
        } catch (DomainException) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->hasher->verify($password, $user->passwordHash)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$user->isVerified()) {
            throw new AuthenticationException('Account is not verified.');
        }

        return new TokenPairDto(
            accessToken: $this->tokenManager->issueAccessToken($user->id),
            refreshToken: $this->tokenManager->issueRefreshToken($user->id),
        );
    }
}
