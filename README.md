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

### chubbyphp-framework

#### Additional Requirements

 * [chubbyphp/chubbyphp-framework][10]: ^1.1.1

#### Example

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\Framework\Application;
use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Swoole\Http\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;

$loader = require __DIR__.'/vendor/autoload.php';

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
    new Application(...)
));

$http->start();

```

### slim 3

#### Additional Requirements

 * [http-interop/http-factory-slim][20]: ^2.0
 * [slim/slim][21]: ^3.12.1

#### Example

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Http\Factory\Slim\ServerRequestFactory;
use Http\Factory\Slim\StreamFactory;
use Http\Factory\Slim\UploadedFileFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Http\Response;
use Swoole\Http\Server;

$loader = require __DIR__.'/vendor/autoload.php';

$app = new App();

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
    new class($app) implements RequestHandlerInterface
    {
        /**
         * @var App
         */
        private $app;

        /**
         * @param App $app
         */
        public function __construct(App $app)
        {
            $this->app = $app;
        }

        /**
         * @param ServerRequestInterface $request
         * @return ResponseInterface
         */
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            return $this->app->process($request, new Response());
        }
    }
));

$http->start();
```

### zend-expressive 3

#### Additional Requirements

 * [zendframework/zend-expressive][30]: ^3.2.1

#### Example

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Swoole\Http\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;
use Zend\Expressive\Application;

$loader = require __DIR__.'/vendor/autoload.php';

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
    new Application(...)
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

[10]: https://packagist.org/packages/chubbyphp/chubbyphp-framework

[20]: https://packagist.org/packages/http-interop/http-factory-slim
[21]: https://packagist.org/packages/slim/slim

[30]: https://packagist.org/packages/zendframework/zend-expressive
