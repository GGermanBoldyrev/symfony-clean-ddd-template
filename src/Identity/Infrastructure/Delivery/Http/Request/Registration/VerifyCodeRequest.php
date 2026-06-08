<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Request\Registration;

use App\Shared\Infrastructure\Delivery\Http\Request\JsonRequestParser;
use Symfony\Component\HttpFoundation\Request;

final readonly class VerifyCodeRequest
{
    private function __construct(
        public string $email,
        public string $code,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $body = JsonRequestParser::parse($request);

        return new self(
            email: JsonRequestParser::requireString($body, 'email'),
            code: JsonRequestParser::requireString($body, 'code'),
        );
    }
}
