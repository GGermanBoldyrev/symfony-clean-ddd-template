<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response\Payload;

use JsonSerializable;

final readonly class ApiSuccessPayload implements JsonSerializable
{
    /**
     * @param object|array<mixed>|null $data
     */
    public function __construct(
        private object|array|null $data = null,
    ) {
    }

    /**
     * @return array{success: true, data: mixed}
     */
    public function jsonSerialize(): array
    {
        return [
            'success' => true,
            'data' => $this->data,
        ];
    }
}
