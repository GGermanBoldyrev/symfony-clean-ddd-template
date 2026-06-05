<?php

declare(strict_types=1);

// tests/Unit/Shared/Infrastructure/Delivery/Http/Subscriber/DomainExceptionSubscriberTest.php

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Subscriber;

use App\Identity\Domain\Exception\User\InvalidEmailException;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\Exception\User\UserNotFoundException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
use App\Identity\Domain\ValueObject\User\Email;
use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Infrastructure\Delivery\Http\Subscriber\DomainExceptionSubscriber;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

final class DomainExceptionSubscriberTest extends TestCase
{
    #[Test]
    #[DataProvider('domainExceptionProvider')]
    public function itMapsDomainExceptionsOnApiRoutes(
        DomainExceptionInterface $exception,
        int $statusCode,
        string $errorCode,
    ): void {
        $event = $this->event('/api/v1/test', $exception);

        (new DomainExceptionSubscriber())($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame($statusCode, $response->getStatusCode());
        self::assertStringContainsString($errorCode, $this->responseContent($response));
    }

    #[Test]
    public function itIgnoresNonApiRoutes(): void
    {
        $event = $this->event('/web', InvalidEmailException::invalidFormat('bad'));

        (new DomainExceptionSubscriber())($event);

        self::assertNull($event->getResponse());
    }

    #[Test]
    public function itIgnoresNonDomainExceptions(): void
    {
        $event = $this->event('/api/v1/test', new RuntimeException('boom'));

        (new DomainExceptionSubscriber())($event);

        self::assertNull($event->getResponse());
    }

    /**
     * @return array<string, array{DomainExceptionInterface, int, string}>
     */
    public static function domainExceptionProvider(): array
    {
        return [
            'user not found' => [UserNotFoundException::withId('id'), 404, 'user.not_found'],
            'verification code not found' => [
                VerificationCodeNotFoundException::forEmail('user@example.com'),
                404,
                'verification_code.not_found',
            ],
            'already verified' => [
                new UserAlreadyVerifiedException(Email::fromString('user@example.com')),
                409,
                'user.already_verified',
            ],
            'max attempts' => [
                MaxAttemptsExceededException::exceeded(),
                429,
                'verification_code.max_attempts_exceeded',
            ],
            'cooldown' => [
                ResendCooldownException::before(new DateTimeImmutable('+1 minute')),
                429,
                'verification_code.resend_cooldown',
            ],
            'validation' => [InvalidEmailException::invalidFormat('bad'), 422, 'user.invalid_email'],
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
