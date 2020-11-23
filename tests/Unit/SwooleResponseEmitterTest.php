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
        /** @var ResponseInterface|MockObject $responseWithoutCookies */
        $responseWithoutCookies = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getHeaders')->with()->willReturn(['Content-Type' => ['application/json']]),
        ]);

        /** @var StreamInterface|MockObject $responseBody */
        $responseBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('isSeekable')->with()->willReturn(true),
            Call::create('rewind')->with(),
            Call::create('eof')->willReturn(false),
            Call::create('read')->with(256)->willReturn('This is the body.'),
            Call::create('eof')->willReturn(true),
        ]);

        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getStatusCode')->with()->willReturn(200),
            Call::create('getReasonPhrase')->with()->willReturn('OK'),
            Call::create('withoutHeader')->with('Set-Cookie')->willReturn($responseWithoutCookies),
            Call::create('getHeader')
                ->with('Set-Cookie')
                ->willReturn(['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly; SameSite=strict']),
            Call::create('getBody')->with()->willReturn($responseBody),
        ]);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class, [
            Call::create('status')->with(200, 'OK'),
            Call::create('header')->with('Content-Type', 'application/json', null),
            Call::create('cookie')
                ->with(
                    'id',
                    'a3fWa',
                    1445412480,
                    '/',
                    '',
                    true,
                    true,
                    'Strict',
                    null
                ),
            Call::create('write')->with('This is the body.'),
            Call::create('end')->with(null),
        ]);

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }

    public function testInvokeWithEmptyBody(): void
    {
        /** @var ResponseInterface|MockObject $responseWithoutCookies */
        $responseWithoutCookies = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getHeaders')->with()->willReturn(['Content-Type' => ['application/json']]),
        ]);

        /** @var StreamInterface|MockObject $responseBody */
        $responseBody = $this->getMockByCalls(StreamInterface::class, [
            Call::create('isSeekable')->with()->willReturn(true),
            Call::create('rewind')->with(),
            Call::create('eof')->willReturn(false),
            Call::create('read')->with(256)->willReturn(''),
            Call::create('eof')->willReturn(true),
        ]);

        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockByCalls(ResponseInterface::class, [
            Call::create('getStatusCode')->with()->willReturn(200),
            Call::create('getReasonPhrase')->with()->willReturn('OK'),
            Call::create('withoutHeader')->with('Set-Cookie')->willReturn($responseWithoutCookies),
            Call::create('getHeader')
                ->with('Set-Cookie')
                ->willReturn(['id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly']),
            Call::create('getBody')->with()->willReturn($responseBody),
        ]);

        /** @var SwooleResponse|MockObject $swooleResponse */
        $swooleResponse = $this->getMockByCalls(SwooleResponse::class, [
            Call::create('status')->with(200, 'OK'),
            Call::create('header')->with('Content-Type', 'application/json', null),
            Call::create('cookie')
                ->with(
                    'id',
                    'a3fWa',
                    1445412480,
                    '/',
                    '',
                    true,
                    true,
                    null,
                    null
                ),
            Call::create('end')->with(null),
        ]);

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }
}
