<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Application\Port\TokenManagerPort;
use App\Identity\Domain\ValueObject\AccessToken;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class JwtAuthenticator extends AbstractAuthenticator
{
    private const string BEARER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly TokenManagerPort $tokenManager,
    ) {
    }

    public function supports(Request $request): bool
    {
        $header = $request->headers->get('Authorization', '');

        return str_starts_with($header, self::BEARER_PREFIX);
    }

    public function authenticate(Request $request): Passport
    {
        $header = $request->headers->get('Authorization', '');
        $raw = trim(substr($header, \strlen(self::BEARER_PREFIX)));

        if ($raw === '') {
            throw new CustomUserMessageAuthenticationException('JWT token is missing.');
        }

        $userId = $this->tokenManager->parseAccessToken(AccessToken::fromString($raw));

        if ($userId === null) {
            throw new CustomUserMessageAuthenticationException('JWT token is invalid or expired.');
        }

        return new SelfValidatingPassport(
            new UserBadge($userId->toString()),
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response {
        // Skip and go to controller
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception,
    ): Response {
        $message = $exception instanceof CustomUserMessageAuthenticationException
            ? $exception->getMessage()
            : $exception->getMessageKey();

        return ApiResponse::error(
            HttpErrorCode::UNAUTHORIZED,
            'auth.unauthorized',
            $message,
        );
    }
}
