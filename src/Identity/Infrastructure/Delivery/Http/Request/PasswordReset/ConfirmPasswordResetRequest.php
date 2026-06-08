<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Request\PasswordReset;

use App\Shared\Infrastructure\Delivery\Http\Request\JsonRequestParser;
use Symfony\Component\HttpFoundation\Request;

final readonly class ConfirmPasswordResetRequest
{
    private function __construct(
        public string $email,
        public string $code,
        public string $newPassword,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $body = JsonRequestParser::parse($request);

        return new self(
            email: JsonRequestParser::requireString($body, 'email'),
            code: JsonRequestParser::requireString($body, 'code'),
            newPassword: JsonRequestParser::requireString($body, 'new_password'),
        );
    }
}
