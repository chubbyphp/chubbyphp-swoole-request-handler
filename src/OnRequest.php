<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class OnRequest
{
    /**
     * @var PsrRequestFactoryInterface
     */
    private $psrRequestFactory;

    /**
     * @var SwooleResponseEmitterInterface
     */
    private $swooleResponseEmitter;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHander;

    public function __construct(
        PsrRequestFactoryInterface $psrRequestFactory,
        SwooleResponseEmitterInterface $swooleResponseEmitter,
        RequestHandlerInterface $requestHander
    ) {
        $this->psrRequestFactory = $psrRequestFactory;
        $this->swooleResponseEmitter = $swooleResponseEmitter;
        $this->requestHander = $requestHander;
    }

    public function __invoke(SwooleRequest $request, SwooleResponse $response): void
    {
        $this->swooleResponseEmitter->emit(
            $this->requestHander->handle($this->psrRequestFactory->create($request)),
            $response
        );
    }
}
