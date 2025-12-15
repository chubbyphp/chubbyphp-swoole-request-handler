<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

interface SwooleResponseEmitterInterface
{
    public const int DEFAULT_CHUNK_SIZE = 131072;

    public function emit(ResponseInterface $response, SwooleResponse $swooleResponse): void;
}
