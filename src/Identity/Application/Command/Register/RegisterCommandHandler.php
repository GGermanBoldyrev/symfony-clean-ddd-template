<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\Register;

use App\Identity\Application\Port\PasswordHasherPort;
use App\Identity\Application\Port\VerificationCodeGeneratorPort;
use App\Identity\Application\Port\VerificationMailerPort;
use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Entity\VerificationCode;
use App\Identity\Domain\Exception\User\UserAlreadyVerifiedException;
use App\Identity\Domain\Exception\VerificationCode\ResendCooldownException;
use App\Identity\Domain\Exception\VerificationCode\ResendNotAllowedYetException;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Identity\Domain\Service\VerificationCodePolicy;
use App\Identity\Domain\ValueObject\User\DataPolicyAcceptedAt;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\User\PlainPassword;
use App\Identity\Domain\ValueObject\User\UserId;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeId;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RegisterCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private VerificationCodeRepositoryInterface $codes,
        private PasswordHasherPort $hasher,
        private VerificationCodeGeneratorPort $codeGenerator,
        private VerificationMailerPort $mailer,
    ) {
    }

    public function __invoke(RegisterCommand $command): void
    {
        $email = Email::fromString($command->email);

        $existingUser = $this->users->findByEmail($email);

        if ($existingUser !== null) {
            if ($existingUser->isVerified()) {
                throw new UserAlreadyVerifiedException($email);
            }

            $this->issueAndSendCode($email);

            return;
        }

        $password = PlainPassword::fromString($command->password);
        $passwordHash = $this->hasher->hash($password);
        $dataPolicyAcceptedAt = DataPolicyAcceptedAt::accept($command->dataPolicy);

        $user = User::register(
            id: UserId::generate(),
            email: $email,
            passwordHash: $passwordHash,
            dataPolicyAcceptedAt: $dataPolicyAcceptedAt,
        );

        $this->users->save($user);
        $this->issueAndSendCode($email);
    }

    private function issueAndSendCode(Email $email): void
    {
        $now = new DateTimeImmutable();
        $expiresAt = VerificationCodePolicy::expiresAt($now);

        $existing = $this->codes->findByEmail($email);

        if ($existing !== null) {
            try {
                $existing->assertCanResend();
            } catch (ResendNotAllowedYetException $e) {
                throw new ResendCooldownException($existing->resendAfter->toDateTimeImmutable());
            }
        }

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
