<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactoryInterface;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * @covers \Chubbyphp\SwooleRequestHandler\OnRequest
 *
 * @internal
 */
final class OnRequestTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        /** @var MockObject|SwooleRequest $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var MockObject|SwooleResponse $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

        /** @var MockObject|ServerRequestInterface $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class);

        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockByCalls(ResponseInterface::class);

        /** @var MockObject|PsrRequestFactoryInterface $psrRequestFactory */
        $psrRequestFactory = $this->getMockByCalls(PsrRequestFactoryInterface::class, [
            Call::create('create')->with($swooleRequest)->willReturn($request),
        ]);

        /** @var MockObject|SwooleResponseEmitterInterface $swooleResponseEmitter */
        $swooleResponseEmitter = $this->getMockByCalls(SwooleResponseEmitterInterface::class, [
            Call::create('emit')->with($response, $swooleResponse),
        ]);

        /** @var MockObject|RequestHandlerInterface $swooleRequestHandler */
        $swooleRequestHandler = $this->getMockByCalls(RequestHandlerInterface::class, [
            Call::create('handle')->with($request)->willReturn($response),
        ]);

        $onRequest = new OnRequest($psrRequestFactory, $swooleResponseEmitter, $swooleRequestHandler);
        $onRequest($swooleRequest, $swooleResponse);
    }
}
