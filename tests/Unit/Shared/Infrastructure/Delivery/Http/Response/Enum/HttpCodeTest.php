<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Response\Enum;

use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpErrorCode;
use App\Shared\Infrastructure\Delivery\Http\Response\Enum\HttpSuccessCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HttpCodeTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectSuccessMessages(): void
    {
        self::assertSame('OK', HttpSuccessCode::OK->defaultMessage());
        self::assertSame('Created', HttpSuccessCode::CREATED->defaultMessage());
        self::assertSame('No Content', HttpSuccessCode::NO_CONTENT->defaultMessage());
    }

    #[Test]
    public function itReturnsCorrectErrorMessages(): void
    {
        self::assertSame('Not Found', HttpErrorCode::NOT_FOUND->defaultMessage());
        self::assertSame('Bad Request', HttpErrorCode::BAD_REQUEST->defaultMessage());
        self::assertSame('Unprocessable Content', HttpErrorCode::UNPROCESSABLE_ENTITY->defaultMessage());
    }
}
