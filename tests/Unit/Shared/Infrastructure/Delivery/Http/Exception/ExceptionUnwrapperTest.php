<?php

declare(strict_types=1);

// tests/Unit/Shared/Infrastructure/Delivery/Http/Exception/ExceptionUnwrapperTest.php

namespace App\Tests\Unit\Shared\Infrastructure\Delivery\Http\Exception;

use App\Shared\Infrastructure\Delivery\Http\Exception\ExceptionUnwrapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class ExceptionUnwrapperTest extends TestCase
{
    #[Test]
    public function itReturnsPlainExceptionAsIs(): void
    {
        $exception = new RuntimeException('plain');

        self::assertSame($exception, ExceptionUnwrapper::unwrap($exception));
    }

    #[Test]
    public function itUnwrapsHandlerFailedException(): void
    {
        $previous = new RuntimeException('inner');
        $exception = new HandlerFailedException(new Envelope(new stdClass()), [$previous]);

        self::assertSame($previous, ExceptionUnwrapper::unwrap($exception));
    }
}
