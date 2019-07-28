# chubbyphp-swoole-request-handler

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-swoole-request-handler.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-swoole-request-handler)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-swoole-request-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-swoole-request-handler/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-swoole-request-handler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/chubbyphp/chubbyphp-swoole-request-handler/?branch=master)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Latest Unstable Version](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/v/unstable)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)

## Description

A request handler adaper for swoole, using PSR 7, PSR 15 and PSR 17.

## Requirements

 * php: ^7.2
 * [ext-swoole][2]: *
 * [dflydev/fig-cookies][3]: ^2.0
 * [psr/http-factory][4]: ^1.0.1
 * [psr/http-message][5]: ^1.0.1
 * [psr/http-server-handler][6]: ^1.0.1

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-swoole-request-handler][1].

## Usage

This example uses [zendframework/zend-diactoros][7].

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Server;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;

$loader = require __DIR__.'/vendor/autoload.php';

$responseFactory = new ResponseFactory();

$http = new Server('localhost', 8080);

$http->on('start', function (Server $server): void {
    echo 'Swoole http server is started at http://localhost:8080'.PHP_EOL;
});

$http->on('request', new OnRequest(
    new PsrRequestFactory(
        new ServerRequestFactory(),
        new StreamFactory(),
        new UploadedFileFactory()
    ),
    new SwooleResponseEmitter(),
    new class($responseFactory) implements RequestHandlerInterface {
        /**
         * @var ResponseFactoryInterface
         */
        private $responseFactory;

        /**
         * @param ResponseFactoryInterface $responseFactory
         */
        public function __construct(ResponseFactoryInterface $responseFactory)
        {
            $this->responseFactory = $responseFactory;
        }

        /**
         * @param ServerRequestInterface $request
         *
         * @return ResponseInterface
         */
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $response = $this->responseFactory->createResponse(200, 'OK');
            $response->getBody()->write('It works!');

            return $response;
        }
    }
));

$http->start();
```

## Copyright

Dominik Zogg 2019

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler
[2]: https://www.swoole.co.uk
[3]: https://packagist.org/packages/dflydev/fig-cookies
[4]: https://packagist.org/packages/psr/http-factory
[5]: https://packagist.org/packages/psr/http-message
[6]: https://packagist.org/packages/psr/http-server-handler
[7]: https://packagist.org/packages/zendframework/zend-diactoros

