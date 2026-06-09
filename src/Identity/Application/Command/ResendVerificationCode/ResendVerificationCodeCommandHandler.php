<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ResendVerificationCode;

use App\Identity\Application\Port\VerificationCodeGeneratorPort;
use App\Identity\Application\Port\VerificationMailerPort;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Identity\Domain\Service\VerificationCodePolicy;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ResendVerificationCodeCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private VerificationCodeRepositoryInterface $codes,
        private VerificationCodeGeneratorPort $codeGenerator,
        private VerificationMailerPort $mailer,
    ) {
    }

    public function __invoke(ResendVerificationCodeCommand $command): void
    {
        $now = new DateTimeImmutable();

        $email = Email::fromString($command->email);
        $user = $this->users->findByEmail($email);

        if ($user === null || $user->isVerified()) {
            return;
        }

        $existing = $this->codes->findByEmail($email);

        if ($existing !== null && !$existing->resendAfter->isAllowed($now)) {
            return;
        }

        $expiresAt = VerificationCodePolicy::expiresAt($now);
        $code = $this->codeGenerator->generate();

        $verificationCode = VerificationCode::issue(
            id: VerificationCodeId::generate(),
            email: $email,
            code: $code,
            maxAttempts: VerificationCodePolicy::maxAttempts(),
            expiresAt: $expiresAt,
            resendAfter: VerificationCodePolicy::resendAfter($now, $expiresAt),
        );

        $this->codes->upsert($verificationCode);
        $this->mailer->sendVerificationCode($email, $code);
    }
}
