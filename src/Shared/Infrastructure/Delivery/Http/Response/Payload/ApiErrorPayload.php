<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response\Payload;

use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use JsonSerializable;

final readonly class ApiErrorPayload implements JsonSerializable
{
    /**
     * @param array<string, array<int, string>>|null $details
     */
    public function __construct(
        private HttpErrorCode $status,
        private string $errorCode,
        private ?string $message = null,
        private ?array $details = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $error = [
            'code' => $this->errorCode,
            'message' => $this->message ?? $this->status->defaultMessage(),
        ];

        if ($this->details !== null) {
            $error['details'] = $this->details;
        }

        return [
            'success' => false,
            'error' => $error,
        ];
    }
}
