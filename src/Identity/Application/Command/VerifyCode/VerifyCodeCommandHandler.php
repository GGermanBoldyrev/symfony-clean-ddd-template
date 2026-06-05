<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\VerifyCode;

use App\Identity\Domain\Exception\VerificationCode\InvalidCodeException;
use App\Identity\Domain\Exception\VerificationCode\MaxAttemptsExceededException;
use App\Identity\Domain\Exception\VerificationCode\VerificationCodeNotFoundException;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Identity\Domain\ValueObject\User\Email;
use App\Identity\Domain\ValueObject\VerificationCode\VerificationCodeValue;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class VerifyCodeCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private VerificationCodeRepositoryInterface $codes,
    ) {
    }

    public function __invoke(VerifyCodeCommand $command): void
    {
        $email = Email::fromString($command->email);
        $input = VerificationCodeValue::fromString($command->code);

        $verificationCode = $this->codes->findByEmail($email);

        if ($verificationCode === null) {
            throw VerificationCodeNotFoundException::forEmail($command->email);
        }

        try {
            $verificationCode->verify($input);
        } catch (InvalidCodeException|MaxAttemptsExceededException $exception) {
            $this->codes->save($verificationCode);

            throw $exception;
        }

        $user = $this->users->findByEmail($email);

        if ($user !== null && !$user->isVerified()) {
            $user->verify();
            $this->users->save($user);
        }

        $this->codes->delete($verificationCode->id);
    }
}
