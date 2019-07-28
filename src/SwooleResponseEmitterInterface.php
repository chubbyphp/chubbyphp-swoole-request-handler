<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

interface SwooleResponseEmitterInterface
{
    public function emit(ResponseInterface $response, SwooleResponse $swooleResponse): void;
}
