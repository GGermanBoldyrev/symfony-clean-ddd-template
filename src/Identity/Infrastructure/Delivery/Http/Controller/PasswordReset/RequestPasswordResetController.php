<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Controller\PasswordReset;

use App\Identity\Application\Command\RequestPasswordReset\RequestPasswordResetCommand;
use App\Identity\Infrastructure\Delivery\Http\Request\PasswordReset\RequestPasswordResetRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/password-reset/request', name: 'auth.password-reset.request', methods: ['POST'])]
final readonly class RequestPasswordResetController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = RequestPasswordResetRequest::fromRequest($request);

        $this->commandBus->dispatch(new RequestPasswordResetCommand(
            email: $parsedRequest->email,
        ));

        // Always 200, do not reveal registered accounts.
        return ApiResponse::success([
            'message' => 'If the account exists and is verified, a new code will be sent.',
        ]);
    }
}
