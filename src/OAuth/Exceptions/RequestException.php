<?php

declare(strict_types=1);

namespace CQ\OAuth\Exceptions;

use Exception;
use Psr\Http\Client\RequestExceptionInterface as ExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class RequestException extends Exception implements ExceptionInterface
{
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
