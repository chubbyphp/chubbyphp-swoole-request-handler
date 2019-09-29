<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

interface OnRequestInterface
{
    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void;
}
