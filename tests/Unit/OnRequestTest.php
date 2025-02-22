<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactoryInterface;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitterInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
    #[DoesNotPerformAssertions]
    public function testInvoke(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var ServerRequestInterface $request */
        $request = $builder->create(ServerRequestInterface::class, []);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, []);

        /** @var PsrRequestFactoryInterface $psrRequestFactory */
        $psrRequestFactory = $builder->create(PsrRequestFactoryInterface::class, [
            new WithReturn('create', [$swooleRequest], $request),
        ]);

        /** @var SwooleResponseEmitterInterface $swooleResponseEmitter */
        $swooleResponseEmitter = $builder->create(SwooleResponseEmitterInterface::class, [
            new WithoutReturn('emit', [$response, $swooleResponse]),
        ]);

        /** @var RequestHandlerInterface $swooleRequestHandler */
        $swooleRequestHandler = $builder->create(RequestHandlerInterface::class, [
            new WithReturn('handle', [$request], $response),
        ]);

        $onRequest = new OnRequest($psrRequestFactory, $swooleResponseEmitter, $swooleRequestHandler);
        $onRequest($swooleRequest, $swooleResponse);
    }
}
