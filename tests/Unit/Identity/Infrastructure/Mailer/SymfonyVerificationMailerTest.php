<?php

declare(strict_types=1);

// tests/Unit/Identity/Infrastructure/Mailer/SymfonyVerificationMailerTest.php

namespace App\Tests\Unit\Identity\Infrastructure\Mailer;

use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use App\Identity\Infrastructure\Mailer\SymfonyVerificationMailer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope as MailerEnvelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\RawMessage;

final class SymfonyVerificationMailerTest extends TestCase
{
    #[Test]
    public function itSendsVerificationEmailWithCode(): void
    {
        $mailer = new RecordingMailer();
        $adapter = new SymfonyVerificationMailer($mailer, 'no-reply@example.com', 'Kinodom');

        $adapter->sendVerificationCode(
            Email::fromString('user@example.com'),
            VerificationCodeValue::fromString('123456'),
        );

        $message = $mailer->lastMessage();
        self::assertInstanceOf(SymfonyEmail::class, $message);
        self::assertSame('no-reply@example.com', $message->getFrom()[0]->getAddress());
        self::assertSame('Kinodom', $message->getFrom()[0]->getName());
        self::assertSame('user@example.com', $message->getTo()[0]->getAddress());
        self::assertSame('Your verification code', $message->getSubject());
        self::assertStringContainsString('123456', (string) $message->getTextBody());
        self::assertStringContainsString('<h2>123456</h2>', (string) $message->getHtmlBody());
    }
}

final class RecordingMailer implements MailerInterface
{
    private ?RawMessage $message = null;

    public function send(RawMessage $message, ?MailerEnvelope $envelope = null): void
    {
        $this->message = $message;
    }

    public function lastMessage(): ?RawMessage
    {
        return $this->message;
    }
}
