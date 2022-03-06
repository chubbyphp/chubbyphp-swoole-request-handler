<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter;

use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class NewRelicOnRequestAdapter implements OnRequestInterface
{
    public function __construct(private OnRequestInterface $onRequest, private string $appname)
    {
    }

    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        newrelic_start_transaction($this->appname);

        $this->onRequest->__invoke($swooleRequest, $swooleResponse);

        newrelic_end_transaction();
    }
}
