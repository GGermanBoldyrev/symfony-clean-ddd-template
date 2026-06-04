<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Identity\Application\Command\Register\RegisterCommand;
use App\Identity\Infrastructure\Delivery\Http\Request\Auth\RegisterRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpSuccessCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/register', name: 'auth.register', methods: ['POST'])]
final readonly class RegisterController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = RegisterRequest::fromRequest($request);

        $this->commandBus->dispatch(new RegisterCommand(
            email: $parsedRequest->email,
            password: $parsedRequest->password,
            dataPolicy: $parsedRequest->dataPolicy,
        ));

        return ApiResponse::success(
            data: ['message' => 'Verification code sent to your email.'],
            status: HttpSuccessCode::CREATED,
        );
    }
}
