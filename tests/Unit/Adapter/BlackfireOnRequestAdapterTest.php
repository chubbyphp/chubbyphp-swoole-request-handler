<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit\Adapter;

use Blackfire\Client;
use Blackfire\Exception\LogicException;
use Blackfire\Exception\RuntimeException;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithException;
use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\SwooleRequestHandler\Adapter\BlackfireOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
    #[DoesNotPerformAssertions]
    public function testInvokeWithoutHeaderWithoutConfigAndWithoutLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Client $client */
        $client = $builder->create(Client::class, []);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client);
        $adapter($swooleRequest, $swooleResponse);
    }

    public function testInvokeWithoutConfigAndWithoutLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithCallback('createProbe', static function (Configuration $config, bool $enable) use ($probe): Probe {
                self::assertTrue($enable);

                return $probe;
            }),
            new WithoutReturn('endProbe', [$probe]),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client);
        $adapter($swooleRequest, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithConfigAndWithLogger(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithReturn('createProbe', [$config, true], $probe),
            new WithoutReturn('endProbe', [$probe]),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, []);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithLogicExceptionOnCreateProbe(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        $exception = new LogicException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithException('createProbe', [$config, true], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithRuntimeExceptionOnCreateProbe(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        $exception = new RuntimeException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithException('createProbe', [$config, true], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithLogicExceptionOnProbeEnd(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        $exception = new LogicException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithReturn('createProbe', [$config, true], $probe),
            new WithException('endProbe', [$probe], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithRuntimeExceptionOnProbeEnd(): void
    {
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, []);
        $swooleRequest->header['x-blackfire-query'] = 'swoole';

        /** @var SwooleResponse $swooleResponse */
        $swooleResponse = $builder->create(SwooleResponse::class, []);

        /** @var OnRequestInterface $onRequest */
        $onRequest = $builder->create(OnRequestInterface::class, [
            new WithoutReturn('__invoke', [$swooleRequest, $swooleResponse]),
        ]);

        /** @var Configuration $config */
        $config = $builder->create(Configuration::class, []);

        /** @var Probe $probe */
        $probe = $builder->create(Probe::class, []);

        $exception = new RuntimeException('Something went wrong');

        /** @var Client $client */
        $client = $builder->create(Client::class, [
            new WithReturn('createProbe', [$config, true], $probe),
            new WithException('endProbe', [$probe], $exception),
        ]);

        /** @var LoggerInterface $logger */
        $logger = $builder->create(LoggerInterface::class, [
            new WithoutReturn('error', ['Blackfire exception: Something went wrong', []]),
        ]);

        $adapter = new BlackfireOnRequestAdapter($onRequest, $client, $config, $logger);
        $adapter($swooleRequest, $swooleResponse);
    }
}
