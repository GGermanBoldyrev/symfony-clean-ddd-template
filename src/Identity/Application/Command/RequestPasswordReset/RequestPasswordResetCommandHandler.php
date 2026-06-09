<?php

namespace App\Identity\Application\Command\RequestPasswordReset;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RequestPasswordResetCommandHandler
{
    public function __invoke(RequestPasswordResetCommand $command) {

    }
}
