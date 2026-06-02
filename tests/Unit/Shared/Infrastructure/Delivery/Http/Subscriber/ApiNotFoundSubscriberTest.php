<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Subscriber\ApiNotFoundSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ApiNotFoundSubscriberTest extends TestCase
{
    #[Test]
    public function itSubscribesToCorrectEvents(): void
    {
        $events = ApiNotFoundSubscriber::getSubscribedEvents();

        self::assertArrayHasKey('kernel.request', $events);
        self::assertArrayHasKey('kernel.exception', $events);
    }

    #[Test]
    public function itSets404OnRootMainRequest(): void
    {
        $kernel = self::createStub(HttpKernelInterface::class);
        $request = Request::create('/');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new ApiNotFoundSubscriber();
        $subscriber->onKernelRequest($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(404, $event->getResponse()->getStatusCode());
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"error":"Not Found"}',
            (string) $event->getResponse()->getContent(),
        );
    }

    #[Test]
    public function itIgnoresSubRequestsOnRootPath(): void
    {
        $kernel = self::createStub(HttpKernelInterface::class);
        $request = Request::create('/');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new ApiNotFoundSubscriber();
        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    #[Test]
    public function itIgnoresNonRootPathsOnRequest(): void
    {
        $kernel = self::createStub(HttpKernelInterface::class);
        $request = Request::create('/api/users');
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new ApiNotFoundSubscriber();
        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    #[Test]
    public function itSets404OnNotFoundException(): void
    {
        $kernel = self::createStub(HttpKernelInterface::class);
        $request = Request::create('/some-path');
        $exception = new NotFoundHttpException('Not Found');
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $subscriber = new ApiNotFoundSubscriber();
        $subscriber->onKernelException($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(404, $event->getResponse()->getStatusCode());
    }

    #[Test]
    public function itIgnoresOtherExceptions(): void
    {
        $kernel = self::createStub(HttpKernelInterface::class);
        $request = Request::create('/some-path');
        $exception = new AccessDeniedHttpException('Forbidden');
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $subscriber = new ApiNotFoundSubscriber();
        $subscriber->onKernelException($event);

        self::assertNull($event->getResponse());
    }
}
