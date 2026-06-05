<?php

declare(strict_types=1);

// tests/Unit/Shared/Application/TestDouble/RecordingMessageBus.php

namespace App\Tests\Unit\Shared\Application\TestDouble;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class RecordingMessageBus implements MessageBusInterface
{
    /** @var list<object> */
    private array $dispatched = [];

    public function __construct(
        private readonly ?object $result = null,
    ) {
    }

    /**
     * @param StampInterface[] $stamps
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $envelope = Envelope::wrap($message, $stamps);
        $this->dispatched[] = $envelope->getMessage();

        if ($this->result === null) {
            return $envelope;
        }

        return $envelope->with(new HandledStamp($this->result, 'test.handler'));
    }

    /**
     * @return list<object>
     */
    public function dispatched(): array
    {
        return $this->dispatched;
    }
}
