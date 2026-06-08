<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\PurgeExpiredVerificationCodes;

use App\Identity\Application\Command\PurgeExpiredVerificationCodes\Dto\PurgeExpiredVerificationCodesResult;
use App\Identity\Domain\Repository\VerificationCodeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PurgeExpiredVerificationCodesCommandHandler
{
    public function __construct(
        private VerificationCodeRepositoryInterface $codes,
    ) {
    }

    public function __invoke(PurgeExpiredVerificationCodesCommand $command): PurgeExpiredVerificationCodesResult
    {
        $deleted = $this->codes->deleteExpired();

        return new PurgeExpiredVerificationCodesResult(deletedCount: $deleted);
    }
}
