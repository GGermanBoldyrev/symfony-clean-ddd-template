<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response\Enum;

use Symfony\Component\HttpFoundation\Response;

enum HttpSuccessCode: int
{
    use HttpStatusMessageTrait;

    case OK = Response::HTTP_OK;
    case CREATED = Response::HTTP_CREATED;
    case ACCEPTED = Response::HTTP_ACCEPTED;
    case NO_CONTENT = Response::HTTP_NO_CONTENT;
}
