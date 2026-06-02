<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response\Enum;

use BackedEnum;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-require-implements BackedEnum
 */
trait HttpStatusMessageTrait
{
    public function defaultMessage(): string
    {
        return Response::$statusTexts[$this->value] ?? 'Unknown Status';
    }
}
