<?php

namespace App\Identity\Infrastructure\Delivery\Http\Controller\PasswordReset;

use App\Identity\Application\Command\ConfirmPasswordReset\ConfirmPasswordResetCommand;
use App\Identity\Infrastructure\Delivery\Http\Request\PasswordReset\ConfirmPasswordResetRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/password-reset/confirm', name: 'auth.password-reset.confirm', methods: ['POST'])]
final readonly class ConfirmPasswordResetController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = ConfirmPasswordResetRequest::fromRequest($request);

        $this->commandBus->dispatch(
            new ConfirmPasswordResetCommand(
                email: $parsedRequest->email,
                code: $parsedRequest->code,
                newPassword: $parsedRequest->newPassword,
            ),
        );

        return ApiResponse::success([
            'message' => 'Password reset successfully.',
        ]);
    }
}
