<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\MockMethod\WithCallback;
use Chubbyphp\Mock\MockMethod\WithException;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockMethod\WithReturnSelf;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Swoole\Http\Request as SwooleRequest;

/**
 * @covers \Chubbyphp\SwooleRequestHandler\PsrRequestFactory
 *
 * @internal
 */
final class PsrRequestFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $bodyString = 'This is the body.';
        $builder = new MockObjectBuilder();

        /** @var SwooleRequest $swooleRequest */
        $swooleRequest = $builder->create(SwooleRequest::class, [
            new WithReturn('rawContent', [], $bodyString),
        ]);

        $swooleRequest->server = [
            'request_method' => 'POST',
            'request_uri' => '/application',
        ];

        $swooleRequest->header = [
            'Content-Type' => 'multipart/form-data',
        ];

        $swooleRequest->cookie = [
            'PHPSESSID' => '537cd1fa-f6c1-41ee-85b2-1abcfd6eafb7',
        ];

        $swooleRequest->get = [
            'trackingId' => '82fa3d6a-3255-4716-8ea0-ed7bd19b7241',
        ];

        $swooleRequest->post = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@gmail.com',
            'lastOccupation' => 'PHP Developer',
        ];

        $swooleRequest->files = [
            'cv' => [
                'name' => 'CV.pdf',
                'type' => 'application/pdf',
                'tmp_name' => '/tmp/php9875842a',
                'error' => 0,
                'size' => 1_048_576,
            ],
            'certificates' => [
                [
                    'name' => 'Advanced PHP 2017.pdf',
                    'type' => 'application/pdf',
                    'tmp_name' => '/tmp/php8d5f55ce',
                    'error' => 0,
                    'size' => 389120,
                ],
                [
                    'name' => 'Advanced Achitecture 2018.pdf',
                    'type' => 'application/pdf',
                    'tmp_name' => '/tmp/php123a6bf6',
                    'error' => 0,
                    'size' => 524288,
                ],
            ],
        ];

        /** @var StreamInterface $requestBody */
        $requestBody = $builder->create(StreamInterface::class, [
            new WithReturn('write', [$bodyString], \strlen($bodyString)),
        ]);

        /** @var StreamInterface $uploadedFileStream1 */
        $uploadedFileStream1 = $builder->create(StreamInterface::class, []);

        /** @var StreamInterface $uploadedFileStream2 */
        $uploadedFileStream2 = $builder->create(StreamInterface::class, []);

        /** @var StreamInterface $uploadedFileStream3 */
        $uploadedFileStream3 = $builder->create(StreamInterface::class, []);

        $uploadedFileException = new \RuntimeException('test');

        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = $builder->create(StreamFactoryInterface::class, [
            new WithReturn('createStreamFromFile', ['/tmp/php9875842a', 'r'], $uploadedFileStream1),
            new WithReturn('createStreamFromFile', ['/tmp/php8d5f55ce', 'r'], $uploadedFileStream2),
            new WithException('createStreamFromFile', ['/tmp/php123a6bf6', 'r'], $uploadedFileException),
            new WithReturn('createStream', [''], $uploadedFileStream3),
        ]);

        /** @var UploadedFileInterface $uploadedFile1 */
        $uploadedFile1 = $builder->create(UploadedFileInterface::class, []);

        /** @var UploadedFileInterface $uploadedFile2 */
        $uploadedFile2 = $builder->create(UploadedFileInterface::class, []);

        /** @var UploadedFileInterface $uploadedFile3 */
        $uploadedFile3 = $builder->create(UploadedFileInterface::class, []);

        /** @var UploadedFileFactoryInterface $uploadedFileFactory */
        $uploadedFileFactory = $builder->create(UploadedFileFactoryInterface::class, [
            new WithReturn('createUploadedFile', [$uploadedFileStream1, 1_048_576, 0, 'CV.pdf', 'application/pdf'], $uploadedFile1),
            new WithReturn('createUploadedFile', [$uploadedFileStream2, 389120, 0, 'Advanced PHP 2017.pdf', 'application/pdf'], $uploadedFile2),
            new WithReturn('createUploadedFile', [$uploadedFileStream3, 524288, 0, 'Advanced Achitecture 2018.pdf', 'application/pdf'], $uploadedFile3),
        ]);

        /** @var ServerRequestInterface $request */
        $request = $builder->create(ServerRequestInterface::class, [
            new WithReturnSelf('withHeader', ['Content-Type', 'multipart/form-data']),
            new WithReturnSelf('withCookieParams', [$swooleRequest->cookie]),
            new WithReturnSelf('withQueryParams', [$swooleRequest->get]),
            new WithReturnSelf('withParsedBody', [$swooleRequest->post]),
            new WithCallback('withUploadedFiles', static function (array $uploadedFiles) use ($uploadedFile1, $uploadedFile2, $uploadedFile3, &$request): ServerRequestInterface {
                Assert::assertArrayHasKey('cv', $uploadedFiles);
                Assert::assertSame($uploadedFile1, $uploadedFiles['cv']);
                Assert::assertArrayHasKey('certificates', $uploadedFiles);
                Assert::assertCount(2, $uploadedFiles['certificates']);
                Assert::assertSame($uploadedFile2, $uploadedFiles['certificates'][0]);
                Assert::assertSame($uploadedFile3, $uploadedFiles['certificates'][1]);

                return $request;
            }),
            new WithReturn('getBody', [], $requestBody),
        ]);

        /** @var ServerRequestFactoryInterface $serverRequestFactory */
        $serverRequestFactory = $builder->create(ServerRequestFactoryInterface::class, [
            new WithReturn(
                'createServerRequest',
                ['POST', '/application', ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/application']],
                $request
            ),
        ]);

        $psrRequestFactory = new PsrRequestFactory($serverRequestFactory, $streamFactory, $uploadedFileFactory);
        $psrRequestFactory->create($swooleRequest);
    }
}
