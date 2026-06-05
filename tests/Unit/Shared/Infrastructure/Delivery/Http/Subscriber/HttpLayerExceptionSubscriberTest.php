<?php

declare(strict_types=1);

// tests/Unit/Shared/Infrastructure/Delivery/Http/Subscriber/HttpLayerExceptionSubscriberTest.php

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Shared\Infrastructure\Delivery\Http\Exception\BadRequestException;
use App\Shared\Infrastructure\Delivery\Http\Subscriber\HttpLayerExceptionSubscriber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

final class HttpLayerExceptionSubscriberTest extends TestCase
{
    #[Test]
    #[DataProvider('httpExceptionProvider')]
    public function itMapsHttpLayerExceptions(Throwable $exception, int $statusCode, string $errorCode): void
    {
        $event = $this->event('/api/v1/test', $exception);

        (new HttpLayerExceptionSubscriber())($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame($statusCode, $response->getStatusCode());
        self::assertStringContainsString($errorCode, $this->responseContent($response));
    }

    #[Test]
    public function itIgnoresNonApiRoutes(): void
    {
        $event = $this->event('/web', BadRequestException::emptyBody());

        (new HttpLayerExceptionSubscriber())($event);

        self::assertNull($event->getResponse());
    }

    #[Test]
    public function itIgnoresUnknownApiExceptions(): void
    {
        $event = $this->event('/api/v1/test', new RuntimeException('boom'));

        (new HttpLayerExceptionSubscriber())($event);

        self::assertNull($event->getResponse());
    }

    /**
     * @return array<string, array{Throwable, int, string}>
     */
    public static function httpExceptionProvider(): array
    {
        return [
            'method not allowed' => [
                new MethodNotAllowedHttpException(['POST'], 'Only POST'),
                405,
                'request.method_not_allowed',
            ],
            'bad request' => [BadRequestException::emptyBody(), 400, 'request.bad_request'],
            'authentication' => [new AuthenticationException('Invalid credentials.'), 401, 'auth.unauthorized'],
            'access denied core' => [new AccessDeniedException('Forbidden'), 401, 'auth.unauthorized'],
            'access denied http' => [new AccessDeniedHttpException('Forbidden'), 401, 'auth.unauthorized'],
        ];
    }

    private function event(string $path, Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            self::createStub(HttpKernelInterface::class),
            Request::create($path),
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );
    }

    private function responseContent(\Symfony\Component\HttpFoundation\Response $response): string
    {
        $content = $response->getContent();
        self::assertIsString($content);

        return $content;
    }
}
