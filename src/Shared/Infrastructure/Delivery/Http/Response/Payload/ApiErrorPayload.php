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
        private ?string $message = null,
        private ?array $details = null,
    ) {
    }

    /**
     * @return array<string, bool|string|array<string, array<int, string>>>
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'success' => false,
            'error' => $this->message ?? $this->status->defaultMessage(),
        ];

        if ($this->details !== null) {
            $payload['details'] = $this->details;
        }

        return $payload;
    }
}
