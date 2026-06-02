<?php

declare(strict_types=1);

namespace App\Tests\Functional\Shared\Infrastructure\Delivery\Http\Subscriber;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiNotFoundSubscriberTest extends WebTestCase
{
    #[Test]
    public function itReturnsStrictJson404OnRootPath(): void
    {
        $client = self::createClient();

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
        $client = self::createClient();

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
        $client = self::createClient();

        $client->request('POST', '/fake-route');

        self::assertResponseStatusCodeSame(404);
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Not Found"}',
            (string) $client->getResponse()->getContent(),
        );
    }
}
