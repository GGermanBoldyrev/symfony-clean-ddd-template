<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Identity\Infrastructure\Delivery\Http\Cookie\AuthCookieFactory;
use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpSuccessCode;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth/logout', name: 'auth.logout', methods: ['POST'])]
final readonly class LogoutController
{
    public function __invoke(): ApiResponse
    {
        $response = ApiResponse::success(status: HttpSuccessCode::NO_CONTENT);
        $response->headers->setCookie(AuthCookieFactory::clearRefreshToken());

        return $response;
    }
}
