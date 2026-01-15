<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Swoole\Http\Request as SwooleRequest;

final class PsrRequestFactory implements PsrRequestFactoryInterface
{
    public function __construct(
        private readonly ServerRequestFactoryInterface $serverRequestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly UploadedFileFactoryInterface $uploadedFileFactory
    ) {}

    public function create(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $server = array_change_key_case($swooleRequest->server ?? [], CASE_UPPER);

        $request = $this->serverRequestFactory->createServerRequest(
            $server['REQUEST_METHOD'] ?? 'GET',
            $server['REQUEST_URI'] ?? '',
            $server
        );

        foreach ($swooleRequest->header ?? [] as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $request = $request->withCookieParams($swooleRequest->cookie ?? []);
        $request = $request->withQueryParams($swooleRequest->get ?? []);
        $request = $request->withParsedBody($swooleRequest->post ?? []);

        /** @var array<string, array{tmp_name?: string, size: null|int, error: int, name: null|string, type: null|string}> $files */
        $files = $swooleRequest->files ?? [];

        $request = $request->withUploadedFiles($this->uploadedFiles($files));

        if (false !== $rawContent = $swooleRequest->rawContent()) {
            $request->getBody()->write($rawContent);
        }

        return $request;
    }

    /**
     * @param array<string, array<string, mixed>> $files
     *
     * @return array<string, mixed>
     */
    private function uploadedFiles(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            if (isset($file['tmp_name'])) {
                /** @var array{tmp_name: string, size: null|int, error: int, name: null|string, type: null|string} $file */
                $uploadedFiles[$key] = $this->createUploadedFile($file);
            } else {
                /** @var array<string, array<string, mixed>> $file */
                $uploadedFiles[$key] = $this->uploadedFiles($file);
            }
        }

        return $uploadedFiles;
    }

    /**
     * @param array{tmp_name: string, size: null|int, error: int, name: null|string, type: null|string} $file
     */
    private function createUploadedFile(array $file): UploadedFileInterface
    {
        try {
            $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
        } catch (\RuntimeException) {
            $stream = $this->streamFactory->createStream();
        }

        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            $file['size'],
            $file['error'],
            $file['name'],
            $file['type']
        );
    }
}
