<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response;

use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpSuccessCode;
use App\Shared\Infrastructure\Delivery\Http\Response\Payload\ApiErrorPayload;
use App\Shared\Infrastructure\Delivery\Http\Response\Payload\ApiSuccessPayload;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponse extends JsonResponse
{
    /**
     * @param object|array<string, mixed>|null $data
     */
    public static function success(
        object|array|null $data = null,
        HttpSuccessCode $status = HttpSuccessCode::OK,
    ): self {
        return new self(new ApiSuccessPayload($data), $status->value);
    }

    /**
     * @param array<string, array<int, string>>|null $details
     */
    public static function error(
        HttpErrorCode $status = HttpErrorCode::BAD_REQUEST,
        string $errorCode = 'internal.error',
        ?string $message = null,
        ?array $details = null,
    ): self {
        return new self(
            new ApiErrorPayload($status, $errorCode, $message, $details),
            $status->value,
        );
    }
}
