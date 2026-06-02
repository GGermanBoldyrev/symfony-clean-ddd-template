<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Delivery\Http\Response\Enum;

use Symfony\Component\HttpFoundation\Response;

enum HttpErrorCode: int
{
    use HttpStatusMessageTrait;

    case BAD_REQUEST = Response::HTTP_BAD_REQUEST;
    case UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;
    case FORBIDDEN = Response::HTTP_FORBIDDEN;
    case NOT_FOUND = Response::HTTP_NOT_FOUND;
    case CONFLICT = Response::HTTP_CONFLICT;
    case UNPROCESSABLE_ENTITY = Response::HTTP_UNPROCESSABLE_ENTITY;
    case TOO_MANY_REQUESTS = Response::HTTP_TOO_MANY_REQUESTS;
    case INTERNAL_SERVER_ERROR = Response::HTTP_INTERNAL_SERVER_ERROR;
}
