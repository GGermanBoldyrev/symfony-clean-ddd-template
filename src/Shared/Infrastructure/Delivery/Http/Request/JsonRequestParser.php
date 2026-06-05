<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Request;

use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

final class JsonRequestParser
{
    /**
     * Decodes the request body as a JSON object.
     *
     * @throws BadRequestException on invalid or non-object JSON
     *
     * @return array<string, mixed>
     */
    public static function parse(Request $request): array
    {
        $content = $request->getContent();

        if ($content === '') {
            throw BadRequestException::emptyBody();
        }

        $data = json_decode($content, true);

        if (!\is_array($data) || json_last_error() !== \JSON_ERROR_NONE) {
            throw BadRequestException::invalidJson();
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws BadRequestException when the field is absent or not a string
     */
    public static function requireString(array $body, string $field): string
    {
        if (!\array_key_exists($field, $body)) {
            throw BadRequestException::missingField($field);
        }

        if (!\is_string($body[$field])) {
            throw BadRequestException::wrongType($field, 'string');
        }

        return $body[$field];
    }

    /**
     * @param array<string, mixed> $body
     *
     * @throws BadRequestException when the field is absent or not a bool
     */
    public static function requireBool(array $body, string $field): bool
    {
        if (!\array_key_exists($field, $body)) {
            throw BadRequestException::missingField($field);
        }

        if (!\is_bool($body[$field])) {
            throw BadRequestException::wrongType($field, 'boolean');
        }

        return $body[$field];
    }
}
