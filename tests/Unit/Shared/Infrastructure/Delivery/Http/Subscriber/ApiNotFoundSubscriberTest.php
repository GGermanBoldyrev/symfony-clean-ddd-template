<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Subscriber\ApiNotFoundSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ApiNotFoundSubscriberTest extends TestCase
{
    private const string NOT_FOUND_JSON = '{"success":false,"error":{"code":"route.not_found","message":"Not Found"}}';

    #[Test]
    public function itHasCorrectEventListenerAttributes(): void
    {
        $reflectionClass = new ReflectionClass(ApiNotFoundSubscriber::class);
        $attributes = $reflectionClass->getAttributes(AsEventListener::class);

        self::assertCount(2, $attributes, 'ApiNotFoundSubscriber must have exactly 2 #[AsEventListener] attributes.');

        $events = [];
        foreach ($attributes as $attribute) {
            $arguments = $attribute->getArguments();
            if (isset($arguments['event'])) {
                $events[] = $arguments['event'];
            }
        }

        self::assertContains('kernel.request', $events);
        self::assertContains('kernel.exception', $events);
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
            self::NOT_FOUND_JSON,
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
