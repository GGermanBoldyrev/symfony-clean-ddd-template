<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Response\Payload;

use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use App\Shared\Infrastructure\Delivery\Http\Response\Payload\ApiErrorPayload;
use App\Shared\Infrastructure\Delivery\Http\Response\Payload\ApiSuccessPayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiPayloadTest extends TestCase
{
    #[Test]
    public function itSerializesSuccessPayloadWithData(): void
    {
        $payload = new ApiSuccessPayload(['id' => 'uuid-123']);
        $expected = [
            'success' => true,
            'data' => ['id' => 'uuid-123'],
        ];

        self::assertSame($expected, $payload->jsonSerialize());
    }

    #[Test]
    public function itSerializesSuccessPayloadWithoutData(): void
    {
        $payload = new ApiSuccessPayload(null);
        $expected = [
            'success' => true,
            'data' => null,
        ];

        self::assertSame($expected, $payload->jsonSerialize());
    }

    #[Test]
    public function itSerializesErrorPayloadWithDefaultMessage(): void
    {
        $payload = new ApiErrorPayload(HttpErrorCode::NOT_FOUND, 'route.not_found');
        $expected = [
            'success' => false,
            'error' => [
                'code' => 'route.not_found',
                'message' => 'Not Found',
            ],
        ];

        self::assertSame($expected, $payload->jsonSerialize());
    }

    #[Test]
    public function itSerializesErrorPayloadWithCustomMessageAndDetails(): void
    {
        $payload = new ApiErrorPayload(
            status: HttpErrorCode::UNPROCESSABLE_ENTITY,
            errorCode: 'validation.failed',
            message: 'Validation failed',
            details: ['email' => ['Invalid format']],
        );

        $expected = [
            'success' => false,
            'error' => [
                'code' => 'validation.failed',
                'message' => 'Validation failed',
                'details' => ['email' => ['Invalid format']],
            ],
        ];

        self::assertSame($expected, $payload->jsonSerialize());
    }
}
