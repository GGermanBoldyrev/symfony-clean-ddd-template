<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetCurrentUser;

use App\Identity\Application\Dto\CurrentUserDto;
use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\User\UserId;
use DateTimeInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCurrentUserQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function __invoke(GetCurrentUserQuery $query): CurrentUserDto
    {
        $userId = UserId::fromString($query->userId);
        $user = $this->users->findById($userId);

        if ($user === null) {
            throw UserNotFoundException::withId($query->userId);
        }

        return new CurrentUserDto(
            id: $user->id->toString(),
            email: $user->email->toString(),
            isVerified: $user->isVerified(),
            verifiedAt: $user->verifiedAt?->toDateTimeImmutable()->format(DateTimeInterface::ATOM),
            dataPolicyAcceptedAt: $user->dataPolicyAcceptedAt->toDateTimeImmutable()->format(DateTimeInterface::ATOM),
        );
    }
}
