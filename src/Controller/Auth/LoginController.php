<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Identity\Application\Command\Login\LoginCommand;
use App\Identity\Application\Dto\TokenPairDto;
use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Infrastructure\Delivery\Http\Cookie\AuthCookieFactory;
use App\Identity\Infrastructure\Delivery\Http\Request\Auth\LoginRequest;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/login', name: 'auth.login', methods: ['POST'])]
final readonly class LoginController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private TokenManagerPort $tokens,
        private bool $secureCookies,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $parsedRequest = LoginRequest::fromRequest($request);

        $envelope = $this->commandBus->dispatch(new LoginCommand(
            email: $parsedRequest->email,
            password: $parsedRequest->password,
        ));

        /** @var TokenPairDto $pair */
        $pair = $envelope->last(HandledStamp::class)?->getResult();

        $response = ApiResponse::success([
            'access_token' => $pair->accessToken,
        ]);

        $response->headers->setCookie(
            AuthCookieFactory::refreshToken(
                value: $pair->refreshToken->toString(),
                ttlSeconds: $this->tokens->refreshTokenTtl(),
                secure: $this->secureCookies,
            ),
        );

        return $response;
    }
}
