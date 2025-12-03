<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter;

use Blackfire\Client;
use Blackfire\Exception\LogicException;
use Blackfire\Exception\RuntimeException;
use Blackfire\Probe;
use Blackfire\Profile\Configuration;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

final class BlackfireOnRequestAdapter implements OnRequestInterface
{
    public function __construct(
        private OnRequestInterface $onRequest,
        private Client $client,
        private Configuration $config = new Configuration(),
        private LoggerInterface $logger = new NullLogger()
    ) {}

    public function __invoke(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        if (!isset($swooleRequest->header['x-blackfire-query'])) {
            $this->onRequest->__invoke($swooleRequest, $swooleResponse);

            return;
        }

        $probe = $this->startProbe();

        $this->onRequest->__invoke($swooleRequest, $swooleResponse);

        if (!$probe instanceof Probe) {
            return;
        }

        $this->endProbe($probe);
    }

    private function startProbe(): ?Probe
    {
        try {
            return $this->client->createProbe($this->config);
        } catch (LogicException|RuntimeException $exception) {
            $this->logger->error(\sprintf('Blackfire exception: %s', $exception->getMessage()));
        }

        return null;
    }

    private function endProbe(Probe $probe): void
    {
        try {
            $this->client->endProbe($probe);
        } catch (LogicException|RuntimeException $exception) {
            $this->logger->error(\sprintf('Blackfire exception: %s', $exception->getMessage()));
        }
    }
}
