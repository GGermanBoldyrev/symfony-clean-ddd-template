<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Response;

use App\Shared\Infrastructure\Delivery\Http\Response\ApiResponse;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpSuccessCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiResponseTest extends TestCase
{
    #[Test]
    public function itCreatesSuccessResponse(): void
    {
        $response = ApiResponse::success(['user' => 'John Doe'], HttpSuccessCode::CREATED);

        self::assertSame(201, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":true,"data":{"user":"John Doe"}}',
            (string) $response->getContent(),
        );
    }

    #[Test]
    public function itCreatesErrorResponseWithCodeAndMessage(): void
    {
        $response = ApiResponse::error(HttpErrorCode::FORBIDDEN, 'auth.forbidden', 'Access Denied');

        self::assertSame(403, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":{"code":"auth.forbidden","message":"Access Denied"}}',
            (string) $response->getContent(),
        );
    }

    #[Test]
    public function itCreatesErrorResponseWithDefaultMessage(): void
    {
        $response = ApiResponse::error(HttpErrorCode::NOT_FOUND, 'route.not_found');

        self::assertSame(404, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":{"code":"route.not_found","message":"Not Found"}}',
            (string) $response->getContent(),
        );
    }
}
