<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Identity\Application\Command\ResendVerificationCode\ResendVerificationCodeCommand;
use App\Identity\Infrastructure\Delivery\Http\Request\Auth\ResendVerificationCodeRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/verify/resend', name: 'auth.verify.resend', methods: ['POST'])]
final readonly class ResendVerificationCodeController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = ResendVerificationCodeRequest::fromRequest($request);

        $this->commandBus->dispatch(new ResendVerificationCodeCommand(
            email: $parsedRequest->email,
        ));

        // Всегда отвечаем 200.
        return ApiResponse::success([
            'message' => 'If the account exists and is unverified, a new code has been sent.',
        ]);
    }
}
