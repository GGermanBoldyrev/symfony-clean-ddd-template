<?php

namespace App\Identity\Application\Command\ConfirmPasswordReset;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ConfirmPasswordResetCommandHandler
{
    public function __invoke(ConfirmPasswordResetCommand $command) {

    }
}
