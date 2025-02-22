<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit;

use Chubbyphp\Mock\MockMethod\WithoutReturn;
use Chubbyphp\Mock\MockMethod\WithReturn;
use Chubbyphp\Mock\MockObjectBuilder;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
    #[DoesNotPerformAssertions]
    public function testInvoke(): void
    {
        /** @var ResponseInterface $responseWithoutCookies */
        $builder = new MockObjectBuilder();

        $responseWithoutCookies = $builder->create(ResponseInterface::class, [
            new WithReturn('getHeaders', [], ['Content-Type' => ['application/json']]),
        ]);

        /** @var StreamInterface $responseBody */
        $responseBody = $builder->create(StreamInterface::class, [
            new WithReturn('isSeekable', [], true),
            new WithoutReturn('rewind', []),
            new WithReturn('eof', [], false),
            new WithReturn('read', [256], 'This is the body.'),
            new WithReturn('eof', [], true),
        ]);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, [
            new WithReturn('getStatusCode', [], 200),
            new WithReturn('getReasonPhrase', [], 'OK'),
            new WithReturn('withoutHeader', ['Set-Cookie'], $responseWithoutCookies),
            new WithReturn('getHeader', ['Set-Cookie'], [
                'id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Domain=some-domain.org; Path=/some/path; Secure; HttpOnly; SameSite=strict',
            ]),
            new WithReturn('getBody', [], $responseBody),
        ]);

        $installedVersion = phpversion('swoole');

        if (version_compare($installedVersion, '6.0.0', '>=')) {
            /** @var SwooleResponse $swooleResponse */
            $swooleResponse = $builder->create(SwooleResponse::class, [
                new WithReturn('status', [200, 'OK'], true),
                new WithReturn('header', ['Content-Type', 'application/json', true], true),
                new WithReturn('cookie', [
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/some/path',
                    'some-domain.org',
                    true,
                    true,
                    'Strict',
                    '',
                    false,
                ], true),
                new WithReturn('write', ['This is the body.'], true),
                new WithReturn('end', [null], true),
            ]);
        } else {
            /** @var SwooleResponse $swooleResponse */
            $swooleResponse = $builder->create(SwooleResponse::class, [
                new WithReturn('status', [200, 'OK'], true),
                new WithReturn('header', ['Content-Type', 'application/json', true], true),
                new WithReturn('cookie', [
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/some/path',
                    'some-domain.org',
                    true,
                    true,
                    'Strict',
                    '',
                ], true),
                new WithReturn('write', ['This is the body.'], true),
                new WithReturn('end', [null], true),
            ]);
        }

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }

    #[DoesNotPerformAssertions]
    public function testInvokeWithEmptyBody(): void
    {
        /** @var ResponseInterface $responseWithoutCookies */
        $builder = new MockObjectBuilder();

        $responseWithoutCookies = $builder->create(ResponseInterface::class, [
            new WithReturn('getHeaders', [], ['Content-Type' => ['application/json']]),
        ]);

        /** @var StreamInterface $responseBody */
        $responseBody = $builder->create(StreamInterface::class, [
            new WithReturn('isSeekable', [], true),
            new WithoutReturn('rewind', []),
            new WithReturn('eof', [], false),
            new WithReturn('read', [256], ''),
            new WithReturn('eof', [], true),
        ]);

        /** @var ResponseInterface $response */
        $response = $builder->create(ResponseInterface::class, [
            new WithReturn('getStatusCode', [], 200),
            new WithReturn('getReasonPhrase', [], 'OK'),
            new WithReturn('withoutHeader', ['Set-Cookie'], $responseWithoutCookies),
            new WithReturn('getHeader', ['Set-Cookie'], [
                'id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly',
            ]),
            new WithReturn('getBody', [], $responseBody),
        ]);

        $installedVersion = phpversion('swoole');

        if (version_compare($installedVersion, '6.0.0', '>=')) {
            /** @var SwooleResponse $swooleResponse */
            $swooleResponse = $builder->create(SwooleResponse::class, [
                new WithReturn('status', [200, 'OK'], true),
                new WithReturn('header', ['Content-Type', 'application/json', true], true),
                new WithReturn('cookie', [
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/',
                    '',
                    true,
                    true,
                    '',
                    '',
                    false,
                ], true),
                new WithReturn('end', [null], true),
            ]);
        } else {
            /** @var SwooleResponse $swooleResponse */
            $swooleResponse = $builder->create(SwooleResponse::class, [
                new WithReturn('status', [200, 'OK'], true),
                new WithReturn('header', ['Content-Type', 'application/json', true], true),
                new WithReturn('cookie', [
                    'id',
                    'a3fWa',
                    1_445_412_480,
                    '/',
                    '',
                    true,
                    true,
                    '',
                    '',
                ], true),
                new WithReturn('end', [null], true),
            ]);
        }

        $swooleResponseEmitter = new SwooleResponseEmitter();
        $swooleResponseEmitter->emit($response, $swooleResponse);
    }
}
