<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter;

use Blackfire\Client;
use Blackfire\Exception\ExceptionInterface;
use Blackfire\Profile\Configuration;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class BlackfireOnRequestAdapter implements OnRequestInterface
{
    /**
     * @var OnRequestInterface
     */
    private $onRequest;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
        try {
            $probe = $this->client->createProbe($this->config);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(sprintf('Blackfire exception: %s', $exception->getMessage()));
        }

        $this->onRequest->__invoke($swooleRequest, $swooleResponse);

        if (!isset($probe)) {
            return;
        }

        try {
            $this->client->endProbe($probe);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(sprintf('Blackfire exception: %s', $exception->getMessage()));
        }
    }
}
