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
    private ServerRequestFactoryInterface $serverRequestFactory;

    private StreamFactoryInterface $streamFactory;

    private UploadedFileFactoryInterface $uploadedFileFactory;

    public function __construct(
        ServerRequestFactoryInterface $serverRequestFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

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
        $request = $request->withUploadedFiles($this->uploadedFiles($swooleRequest->files ?? []));

        $request->getBody()->write($swooleRequest->rawContent());

        return $request;
    }

    /**
     * @param array<string, array<string, int|string>> $files
     *
     * @return array<string, UploadedFileInterface>
     */
    private function uploadedFiles(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $key => $file) {
            if (isset($file['tmp_name'])) {
                $uploadedFiles[$key] = $this->createUploadedFile($file);
            } else {
                $uploadedFiles[$key] = $this->uploadedFiles($file);
            }
        }

        return $uploadedFiles;
    }

    /**
     * @param array<string, int|string> $file
     */
    private function createUploadedFile(array $file): UploadedFileInterface
    {
        try {
            $stream = $this->streamFactory->createStreamFromFile($file['tmp_name']);
        } catch (\RuntimeException $exception) {
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
