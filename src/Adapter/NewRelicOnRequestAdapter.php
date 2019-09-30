<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter;

use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class NewRelicOnRequestAdapter implements OnRequestInterface
{
    /**
     * @var string
     */
    private $appname;

    /**
     * @var OnRequestInterface
     */
    private $onRequest;

    public function __construct(string $appname, OnRequestInterface $onRequest)
    {
        $this->appname = $appname;
        $this->onRequest = $onRequest;
    }

    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        if (!extension_loaded('newrelic')) {
            $this->onRequest->__invoke($swooleRequest, $swooleResponse);

            return;
        }

        newrelic_start_transaction($this->appname);

        $this->onRequest->__invoke($swooleRequest, $swooleResponse);

        newrelic_end_transaction();
    }
}
