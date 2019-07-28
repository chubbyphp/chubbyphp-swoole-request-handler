<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

final class SwooleResponseEmitter implements SwooleResponseEmitterInterface
{
    public function emit(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());

        foreach ($response->withoutHeader(SetCookies::SET_COOKIE_HEADER)->getHeaders() as $name => $values) {
            $swooleResponse->header($name, implode(', ', $values));
        }

        foreach (SetCookies::fromResponse($response)->getAll() as $setCookie) {
            $this->mapCookie($swooleResponse, $setCookie);
        }

        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            $swooleResponse->write($body->read(256));
        }

        $swooleResponse->end();
    }

    private function mapCookie(SwooleResponse $swooleResponse, SetCookie $cookie): void
    {
        $swooleResponse->cookie(
            $cookie->getName(),
            $cookie->getValue(),
            $cookie->getExpires(),
            $cookie->getPath() ? $cookie->getPath() : '/',
            $cookie->getDomain() ? $cookie->getDomain() : '',
            $cookie->getSecure(),
            $cookie->getHttpOnly()
        );
    }
}
