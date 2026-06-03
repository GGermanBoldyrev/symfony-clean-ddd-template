<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Identity\Application\Command\RefreshToken\RefreshTokenCommand;
use App\Identity\Application\Dto\TokenPairDto;
use App\Identity\Application\Port\TokenManagerInterface;
use App\Shared\Infrastructure\Delivery\Http\Cookie\AuthCookieFactory;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

use function is_string;

#[Route('/api/v1/auth/refresh', name: 'auth.refresh', methods: ['POST'])]
final readonly class RefreshController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private TokenManagerInterface $tokens, // TODO
        private bool $secureCookies,
    ) {
    }

    public function __invoke(Request $request): ApiResponse
    {
        $cookieValue = $request->cookies->get(AuthCookieFactory::REFRESH_TOKEN_COOKIE);

        if (!is_string($cookieValue) || $cookieValue === '') {
            return ApiResponse::error(
                HttpErrorCode::UNAUTHORIZED,
                'Refresh token cookie is missing.',
            );
        }

        $envelope = $this->commandBus->dispatch(
            new RefreshTokenCommand(refreshToken: $cookieValue),
        );

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
