<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

final class SwooleResponseEmitter implements SwooleResponseEmitterInterface
{
    public function __construct(
        private int $chunkSize = self::DEFAULT_CHUNK_SIZE
    ) {}

    public function emit(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());

        /** @var string $name */
        foreach ($response->withoutHeader(SetCookies::SET_COOKIE_HEADER)->getHeaders() as $name => $values) {
            $swooleResponse->header($name, implode(', ', $values));
        }

        foreach (SetCookies::fromResponse($response)->getAll() as $setCookie) {
            $this->mapCookie($swooleResponse, $setCookie);
        }

        $this->mapBody($response, $swooleResponse);

        $swooleResponse->end();
    }

    private function mapCookie(SwooleResponse $swooleResponse, SetCookie $cookie): void
    {
        $swooleResponse->cookie(
            $cookie->getName(),
            $cookie->getValue() ?? '',
            $cookie->getExpires(),
            $cookie->getPath() ?? '/',
            $cookie->getDomain() ?? '',
            $cookie->getSecure(),
            $cookie->getHttpOnly(),
            $this->mapSameSite($cookie)
        );
    }

    private function mapSameSite(SetCookie $cookie): string
    {
        if (null === $sameSite = $cookie->getSameSite()) {
            return '';
        }

        return substr($sameSite->asString(), 9);
    }

    private function mapBody(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            if ('' !== $chunk = $body->read($this->chunkSize)) {
                $swooleResponse->write($chunk);
            }
        }
    }
}
