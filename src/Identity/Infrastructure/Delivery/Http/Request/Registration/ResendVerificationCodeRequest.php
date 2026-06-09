<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Request\Registration;

use App\Shared\Infrastructure\Delivery\Http\Request\JsonRequestParser;
use Symfony\Component\HttpFoundation\Request;

final readonly class ResendVerificationCodeRequest
{
    private function __construct(
        public string $email,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $body = JsonRequestParser::parse($request);

        return new self(
            email: JsonRequestParser::requireString($body, 'email'),
        );
    }
}
