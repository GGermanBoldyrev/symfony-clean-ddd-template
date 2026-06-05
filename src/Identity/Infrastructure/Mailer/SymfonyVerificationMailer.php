<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Mailer;

use App\Identity\Application\Port\VerificationMailerPort;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

final class SymfonyVerificationMailer implements VerificationMailerPort
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }

    public function sendVerificationCode(Email $to, VerificationCodeValue $code): void
    {
        $message = new SymfonyEmail()
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($to->toString())
            ->subject('Your verification code')
            ->text($this->buildTextBody($code))
            ->html($this->buildHtmlBody($code));

        $this->mailer->send($message);
    }

    private function buildTextBody(VerificationCodeValue $code): string
    {
        return \sprintf(
            "Your verification code is: %s\n\nThe code is valid for 15 minutes.\nDo not share this code with anyone.",
            $code->toString(),
        );
    }

    private function buildHtmlBody(VerificationCodeValue $code): string
    {
        return \sprintf(
            '<p>Your verification code is:</p><h2>%s</h2><p>The code is valid for 15 minutes.<br>Do not share this code with anyone.</p>',
            htmlspecialchars($code->toString(), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8'),
        );
    }
}
