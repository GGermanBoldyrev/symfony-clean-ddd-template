<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Delivery\Http\Request\Auth;

use App\Shared\Infrastructure\Delivery\Http\Request\JsonRequestParser;
use Symfony\Component\HttpFoundation\Request;

final readonly class RegisterRequest
{
    private function __construct(
        public string $email,
        public string $password,
        public bool $dataPolicy,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $body = JsonRequestParser::parse($request);

        return new self(
            email: JsonRequestParser::requireString($body, 'email'),
            password: JsonRequestParser::requireString($body, 'password'),
            dataPolicy: JsonRequestParser::requireBool($body, 'data_policy'),
        );
    }
}
