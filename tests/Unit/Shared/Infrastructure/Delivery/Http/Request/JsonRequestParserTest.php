<?php

declare(strict_types=1);

// tests/Unit/Shared/Infrastructure/Delivery/Http/Request/JsonRequestParserTest.php

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Request;

use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use App\Shared\Infrastructure\Delivery\Http\Request\JsonRequestParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JsonRequestParserTest extends TestCase
{
    #[Test]
    public function itParsesJsonObjectBody(): void
    {
        $body = JsonRequestParser::parse($this->request('{"email":"user@example.com","data_policy":true}'));

        self::assertSame('user@example.com', $body['email']);
        self::assertTrue($body['data_policy']);
    }

    #[Test]
    public function itRejectsEmptyBody(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Request body must not be empty.');

        JsonRequestParser::parse($this->request(''));
    }

    #[Test]
    public function itRejectsInvalidJson(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Request body contains invalid JSON.');

        JsonRequestParser::parse($this->request('{"email":'));
    }

    #[Test]
    public function itRejectsNonObjectJson(): void
    {
        $this->expectException(BadRequestException::class);

        JsonRequestParser::parse($this->request('["user@example.com"]'));
    }

    #[Test]
    public function itRequiresStringField(): void
    {
        self::assertSame('user@example.com', JsonRequestParser::requireString(['email' => 'user@example.com'], 'email'));
    }

    #[Test]
    public function itRejectsMissingStringField(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Required field "email" is missing.');

        JsonRequestParser::requireString([], 'email');
    }

    #[Test]
    public function itRejectsWrongStringFieldType(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Field "email" must be of type string.');

        JsonRequestParser::requireString(['email' => 123], 'email');
    }

    #[Test]
    public function itRequiresBooleanField(): void
    {
        self::assertTrue(JsonRequestParser::requireBool(['data_policy' => true], 'data_policy'));
    }

    #[Test]
    public function itRejectsWrongBooleanFieldType(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Field "data_policy" must be of type boolean.');

        JsonRequestParser::requireBool(['data_policy' => 'true'], 'data_policy');
    }

    private function request(string $content): Request
    {
        return Request::create('/', 'POST', [], [], [], [], $content);
    }
}
