<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\Argument\ArgumentCallback;
use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use PHPUnit\Framework\MockObject\MockObject;
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
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        /** @var SwooleRequest|MockObject $swooleRequest */
        $swooleRequest = $this->getMockByCalls(SwooleRequest::class, [
            Call::create('rawContent')->with()->willReturn('This is the body.'),
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
                'size' => 1048576,
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

        /** @var StreamInterface|MockObject $requestBody */
        $requestBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('write')->with('This is the body.'),
        ]);

        /** @var StreamInterface|MockObject $uploadedFileStream1 */
        $uploadedFileStream1 = $this->getMockByCalls(StreamInterface::class);

        /** @var StreamInterface|MockObject $uploadedFileStream2 */
        $uploadedFileStream2 = $this->getMockByCalls(StreamInterface::class);

        /** @var StreamInterface|MockObject $uploadedFileStream3 */
        $uploadedFileStream3 = $this->getMockByCalls(StreamInterface::class);

        $uploadedFileException = new \RuntimeException('test');

        /** @var StreamFactoryInterface|MockObject $streamFactory */
        $streamFactory = $this->getMockByCalls(StreamFactoryInterface::class, [
            Call::create('createStreamFromFile')->with('/tmp/php9875842a', 'r')->willReturn($uploadedFileStream1),
            Call::create('createStreamFromFile')->with('/tmp/php8d5f55ce', 'r')->willReturn($uploadedFileStream2),
            Call::create('createStreamFromFile')
                ->with('/tmp/php123a6bf6', 'r')
                ->willThrowException($uploadedFileException),
            Call::create('createStream')->with('')->willReturn($uploadedFileStream3),
        ]);

        /** @var UploadedFileInterface|MockObject $uploadedFile1 */
        $uploadedFile1 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var UploadedFileInterface|MockObject $uploadedFile2 */
        $uploadedFile2 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var UploadedFileInterface|MockObject $uploadedFile3 */
        $uploadedFile3 = $this->getMockByCalls(UploadedFileInterface::class);

        /** @var UploadedFileFactoryInterface|MockObject $uploadedFileFactory */
        $uploadedFileFactory = $this->getMockByCalls(UploadedFileFactoryInterface::class, [
            Call::create('createUploadedFile')
                ->with($uploadedFileStream1, 1048576, 0, 'CV.pdf', 'application/pdf')
                ->willReturn($uploadedFile1),
            Call::create('createUploadedFile')
                ->with($uploadedFileStream2, 389120, 0, 'Advanced PHP 2017.pdf', 'application/pdf')
                ->willReturn($uploadedFile2),
            Call::create('createUploadedFile')
                ->with($uploadedFileStream3, 524288, 0, 'Advanced Achitecture 2018.pdf', 'application/pdf')
                ->willReturn($uploadedFile3),
        ]);

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockByCalls(ServerRequestInterface::class, [
            Call::create('withHeader')->with('Content-Type', 'multipart/form-data')->willReturnSelf(),
            Call::create('withCookieParams')->with($swooleRequest->cookie)->willReturnSelf(),
            Call::create('withQueryParams')->with($swooleRequest->get)->willReturnSelf(),
            Call::create('withParsedBody')->with($swooleRequest->post)->willReturnSelf(),
            Call::create('withUploadedFiles')
                ->with(new ArgumentCallback(
                    function (array $uploadedFiles) use ($uploadedFile1, $uploadedFile2, $uploadedFile3): void {
                        self::assertArrayHasKey('cv', $uploadedFiles);

                        self::assertSame($uploadedFile1, $uploadedFiles['cv']);

                        self::assertArrayHasKey('certificates', $uploadedFiles);

                        self::assertCount(2, $uploadedFiles['certificates']);

                        self::assertSame($uploadedFile2, $uploadedFiles['certificates'][0]);
                        self::assertSame($uploadedFile3, $uploadedFiles['certificates'][1]);
                    }
                ))
                ->willReturnSelf(),
            Call::create('getBody')->with()->willReturn($requestBody),
        ]);

        /** @var ServerRequestFactoryInterface|MockObject $serverRequestFactory */
        $serverRequestFactory = $this->getMockByCalls(ServerRequestFactoryInterface::class, [
            Call::create('createServerRequest')
                ->with('POST', '/application', ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/application'])
                ->willReturn($request),
        ]);

        $psrRequestFactory = new PsrRequestFactory($serverRequestFactory, $streamFactory, $uploadedFileFactory);
        $psrRequestFactory->create($swooleRequest);
    }
}
