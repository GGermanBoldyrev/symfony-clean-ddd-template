<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Controller\Authentication;

use App\Identity\Application\Dto\CurrentUserDto;
use App\Identity\Application\Query\GetCurrentUser\GetCurrentUserQuery;
use App\Identity\Infrastructure\Security\SecurityUser;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/auth/me', name: 'auth.me', methods: ['GET'])]
final readonly class MeController
{
    public function __construct(
        private MessageBusInterface $queryBus,
    ) {
    }

    public function __invoke(#[CurrentUser] SecurityUser $securityUser): ApiResponse
    {
        $envelope = $this->queryBus->dispatch(
            new GetCurrentUserQuery(userId: $securityUser->getUserIdentifier()),
        );

        /** @var CurrentUserDto $dto */
        $dto = $envelope->last(HandledStamp::class)?->getResult();

        return ApiResponse::success([
            'id' => $dto->id,
            'email' => $dto->email,
            'is_verified' => $dto->isVerified,
            'verified_at' => $dto->verifiedAt,
            'data_policy_accepted_at' => $dto->dataPolicyAcceptedAt,
        ]);
    }
}
