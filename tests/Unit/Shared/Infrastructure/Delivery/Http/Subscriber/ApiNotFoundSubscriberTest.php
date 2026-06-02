<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Subscriber;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiNotFoundSubscriberTest extends WebTestCase
{
    #[Test]
    public function itReturnsStrictJson404OnRootPath(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Not Found"}',
            (string) $client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itReturnsStrictJson404OnNonExistentRoute(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/some-fake-endpoint-that-does-not-exist');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Not Found"}',
            (string) $client->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itAllowsPostMethodsToReturn404Correctly(): void
    {
        $client = static::createClient();

        $client->request('POST', '/fake-route');

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Not Found"}',
            (string) $client->getResponse()->getContent(),
        );
    }
}
