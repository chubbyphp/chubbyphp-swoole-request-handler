<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\Call;
use Chubbyphp\Mock\MockByCallsTrait;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response as SwooleResponse;

/**
 * @covers \Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter
 *
 * @internal
 */
final class SwooleResponseEmitterTest extends TestCase
{
    use MockByCallsTrait;

    public function testInvoke(): void
    {
        /** @var MockObject|ResponseInterface $responseWithoutCookies */
        $responseWithoutCookies = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getHeaders')->with()->willReturn(['Content-Type' => ['application/json']]),
        ]);

        /** @var MockObject|StreamInterface $responseBody */
        $responseBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('isSeekable')->with()->willReturn(true),
            Call::create('rewind')->with(),
            Call::create('eof')->willReturn(false),
            Call::create('read')->with(256)->willReturn('This is the body.'),
            Call::create('eof')->willReturn(true),
        ]);

        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getStatusCode')->with()->willReturn(200),
            Call::create('getReasonPhrase')->with()->willReturn('OK'),
            Call::create('withoutHeader')->with('Set-Cookie')->willReturn($responseWithoutCookies),
            Call::create('getHeader')
                ->with('Set-Cookie')
                ->willReturn(['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Domain=some-domain.org; Path=/some/path; Secure; HttpOnly; SameSite=strict']),
            Call::create('getBody')->with()->willReturn($responseBody),
        ]);

        /** @var MockObject|SwooleResponse $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class, [
            Call::create('status')->with(200, 'OK')->willReturn(true),
            Call::create('header')->with('Content-Type', 'application/json', true)->willReturn(true),
            Call::create('cookie')
                ->with(
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/some/path',
                    'some-domain.org',
                    true,
                    true,
                    'Strict',
                    ''
                )
                ->willReturn(true),
            Call::create('write')->with('This is the body.')->willReturn(true),
            Call::create('end')->with(null)->willReturn(true),
        ]);

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }

    public function testInvokeWithEmptyBody(): void
    {
        /** @var MockObject|ResponseInterface $responseWithoutCookies */
        $responseWithoutCookies = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getHeaders')->with()->willReturn(['Content-Type' => ['application/json']]),
        ]);

        /** @var MockObject|StreamInterface $responseBody */
        $responseBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('isSeekable')->with()->willReturn(true),
            Call::create('rewind')->with(),
            Call::create('eof')->willReturn(false),
            Call::create('read')->with(256)->willReturn(''),
            Call::create('eof')->willReturn(true),
        ]);

        /** @var MockObject|ResponseInterface $response */
        $response = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getStatusCode')->with()->willReturn(200),
            Call::create('getReasonPhrase')->with()->willReturn('OK'),
            Call::create('withoutHeader')->with('Set-Cookie')->willReturn($responseWithoutCookies),
            Call::create('getHeader')
                ->with('Set-Cookie')
                ->willReturn(['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly']),
            Call::create('getBody')->with()->willReturn($responseBody),
        ]);

        /** @var MockObject|SwooleResponse $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class, [
            Call::create('status')->with(200, 'OK')->willReturn(true),
            Call::create('header')->with('Content-Type', 'application/json', true)->willReturn(true),
            Call::create('cookie')
                ->with(
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/',
                    '',
                    true,
                    true,
                    '',
                    ''
                )->willReturn(true),
            Call::create('end')->with(null)->willReturn(true),
        ]);

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }
}
