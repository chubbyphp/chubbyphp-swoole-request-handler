<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit\Adapter;

use Blackfire\Client;
use Blackfire\Exception\LogicException;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\Mock\Argument\ArgumentInstanceOf;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\SwooleRequestHandler\Adapter\BlackfireOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * @covers \Chubbyphp\SwooleRequestHandler\Adapter\BlackfireOnRequestAdapter
 *
 * @internal
 */
final class BlackfireOnRequestAdapterTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvokeWithoutConficAndWithoutLogger(): void
    {
        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

        /** @var OnRequestInterface|MockObject $onRequest */
        $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
            Call::create('__invoke')->with($swooleRequest, $swooleResponse),
        ]);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with(new ArgumentInstanceOf(Configuration::class), true)->willReturn($probe),
            Call::create('endProbe')->with($probe),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client);
        $adapter($swooleRequest, $swooleResponse);
    }

    public function testInvokeWithConficAndWithLogger(): void
    {
        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

        /** @var OnRequestInterface|MockObject $onRequest */
        $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
            Call::create('__invoke')->with($swooleRequest, $swooleResponse),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willReturn($probe),
            Call::create('endProbe')->with($probe),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    public function testInvokeWithExceptionOnCreateProbe(): void
    {
        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

        /** @var OnRequestInterface|MockObject $onRequest */
        $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
            Call::create('__invoke')->with($swooleRequest, $swooleResponse),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        $exception = new LogicException('Something went wrong');

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willThrowException($exception),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class, [
            Call::create('error')->with('Blackfire exception: Something went wrong', []),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    public function testInvokeWithExceptionOnProbeEnd(): void
    {
        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

        /** @var OnRequestInterface|MockObject $onRequest */
        $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
            Call::create('__invoke')->with($swooleRequest, $swooleResponse),
        ]);

        /** @var Configuration|MockObject $config */
        $config = $this->getMockByCalls(Configuration::class);

        /** @var Probe|MockObject $probe */
        $probe = $this->getMockByCalls(Probe::class);

        $exception = new LogicException('Something went wrong');

        /** @var Client|MockObject $client */
        $client = $this->getMockByCalls(Client::class, [
            Call::create('createProbe')->with($config, true)->willReturn($probe),
            Call::create('endProbe')->with($probe)->willThrowException($exception),
        ]);

        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockByCalls(LoggerInterface::class, [
            Call::create('error')->with('Blackfire exception: Something went wrong', []),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }
}
