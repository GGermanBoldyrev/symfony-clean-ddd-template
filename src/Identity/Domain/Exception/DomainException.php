<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use RuntimeException;

/**
 * Base for all Identity domain exceptions.
 */
abstract class DomainException extends RuntimeException implements DomainExceptionInterface
{
}
