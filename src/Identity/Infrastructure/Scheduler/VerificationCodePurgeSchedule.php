<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Scheduler;

use App\Identity\Application\Command\PurgeExpiredVerificationCodes\PurgeExpiredVerificationCodesCommand;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule(name: 'verification_code_purge')]
final class VerificationCodePurgeSchedule implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= new Schedule()
            ->stateful($this->cache)
            ->add(
                RecurringMessage::every('15 minutes', new PurgeExpiredVerificationCodesCommand()),
            );
    }
}
