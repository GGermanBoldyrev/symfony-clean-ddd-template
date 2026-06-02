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
    public function itCreatesErrorResponse(): void
    {
        $response = ApiResponse::error(HttpErrorCode::FORBIDDEN, 'Access Denied');

        self::assertSame(403, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Access Denied"}',
            (string) $response->getContent(),
        );
    }
}
