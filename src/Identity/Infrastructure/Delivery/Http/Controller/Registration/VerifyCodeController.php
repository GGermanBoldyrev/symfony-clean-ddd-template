<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Controller\Registration;

use App\Identity\Application\Command\VerifyCode\VerifyCodeCommand;
use App\Identity\Infrastructure\Delivery\Http\Request\Registration\VerifyCodeRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/verify', name: 'auth.verify', methods: ['POST'])]
final readonly class VerifyCodeController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = VerifyCodeRequest::fromRequest($request);

        $this->commandBus->dispatch(new VerifyCodeCommand(
            email: $parsedRequest->email,
            code: $parsedRequest->code,
        ));

        return ApiResponse::success(['message' => 'Account verified successfully.']);
    }
}
