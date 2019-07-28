<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;

interface PsrRequestFactoryInterface
{
    public function create(SwooleRequest $swooleRequest): ServerRequestInterface;
}
