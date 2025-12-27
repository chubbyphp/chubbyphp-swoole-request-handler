<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class OnRequest implements OnRequestInterface
{
    public function __construct(
        private readonly PsrRequestFactoryInterface $psrRequestFactory,
        private readonly SwooleResponseEmitterInterface $swooleResponseEmitter,
        private readonly RequestHandlerInterface $requestHander
    ) {}

    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $this->swooleResponseEmitter->emit(
            $this->requestHander->handle($this->psrRequestFactory->create($swooleRequest)),
            $swooleResponse
        );
    }
}
