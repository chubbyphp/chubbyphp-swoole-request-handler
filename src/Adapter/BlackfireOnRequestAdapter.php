<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter;

use Blackfire\Client;
use Blackfire\Exception\ExceptionInterface;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class BlackfireOnRequestAdapter implements OnRequestInterface
{
    private OnRequestInterface $onRequest;

    private Client $client;

    private Configuration $config;

    private LoggerInterface $logger;

    public function __construct(
        OnRequestInterface $onRequest,
        Client $client,
        ?Configuration $config = null,
        ?LoggerInterface $logger = null
    ) {
        $this->onRequest = $onRequest;
        $this->client = $client;
        $this->config = $config ?? new Configuration();
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        if (!isset($swooleRequest->header['x-blackfire-query'])) {
            $this->onRequest->__invoke($swooleRequest, $swooleResponse);

            return;
        }

        $probe = $this->startProbe();

        $this->onRequest->__invoke($swooleRequest, $swooleResponse);

        if (null === $probe) {
            return;
        }

        $this->endProbe($probe);
    }

    private function startProbe(): ?Probe
    {
        try {
            return $this->client->createProbe($this->config);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(sprintf('Blackfire exception: %s', $exception->getMessage()));
        }

        return null;
    }

    private function endProbe(Probe $probe): void
    {
        try {
            $this->client->endProbe($probe);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(sprintf('Blackfire exception: %s', $exception->getMessage()));
        }
    }
}
