# chubbyphp-swoole-request-handler

[![Build Status](https://api.travis-ci.org/chubbyphp/chubbyphp-swoole-request-handler.png?branch=master)](https://travis-ci.org/chubbyphp/chubbyphp-swoole-request-handler)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-swoole-request-handler/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-swoole-request-handler?branch=master)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/chubbyphp/chubbyphp-swoole-request-handler/master)](https://travis-ci.org/chubbyphp/chubbyphp-swoole-request-handler)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/v/stable.png)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/downloads.png)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Monthly Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/d/monthly)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)

[![bugs](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=bugs)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![code_smells](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=code_smells)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![coverage](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=coverage)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![duplicated_lines_density](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![ncloc](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=ncloc)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![sqale_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![alert_status](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=alert_status)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![reliability_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![security_rating](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=security_rating)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![sqale_index](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)
[![vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=chubbyphp_chubbyphp-swoole-request-handler&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=chubbyphp_chubbyphp-swoole-request-handler)

## Description

A request handler adapter for swoole, using PSR-7, PSR-15 and PSR-17.

## Requirements

 * php: ^7.2|^8.0
 * [ext-swoole][2]: ^4.4.8
 * [dflydev/fig-cookies][3]: ^2.0
 * [psr/http-factory][4]: ^1.0.1
 * [psr/http-message][5]: ^1.0.1
 * [psr/http-server-handler][6]: ^1.0.1

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-swoole-request-handler][1].

```sh
composer require chubbyphp/chubbyphp-swoole-request-handler "^1.0"
```

## Usage

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\WorkermanRequestHandler\OnRequest;
use Chubbyphp\WorkermanRequestHandler\PsrRequestFactory;
use Chubbyphp\WorkermanRequestHandler\WorkermanResponseEmitter;
use Psr\Http\Server\RequestHandlerInterface;
use Some\Psr17\Factory\ServerRequestFactory;
use Some\Psr17\Factory\StreamFactory;
use Some\Psr17\Factory\UploadedFileFactory;
use Swoole\Http\Server;

$loader = require __DIR__.'/vendor/autoload.php';

/** @var RequestHandlerInterface $app*/
$app = ...;

$http = new Server('0.0.0.0', 8080);

$http->on('start', function (Server $server): void {
    echo 'Swoole http server is started at http://0.0.0.0:8080'.PHP_EOL;
});

$http->on('request', new OnRequest(
    new PsrRequestFactory(
        new ServerRequestFactory(),
        new StreamFactory(),
        new UploadedFileFactory()
    ),
    new SwooleResponseEmitter(),
    $app
));

$http->start();
```

### with blackfire

```php
<?php

declare(strict_types=1);

namespace App;

use Blackfire\Client;
use Chubbyphp\SwooleRequestHandler\Adapter\BlackfireOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequest;

/** @var OnRequest $onRequest */
$onRequest = ...;

if (extension_loaded('blackfire') {
    $onRequest = new BlackfireOnRequestAdapter($onRequest, new Client());
}

$http->on('request', $onRequest);
```

### with newrelic

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\Adapter\NewRelicOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequest;

/** @var OnRequest $onRequest */
$onRequest = ...;

if (extension_loaded('newrelic') && false !== $name = ini_get('newrelic.appname')) {
    $onRequest = new NewRelicOnRequestAdapter($onRequest, $name);
}

$http->on('request', $onRequest);
```

## Copyright

Dominik Zogg 2020

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler
[2]: https://www.swoole.co.uk
[3]: https://packagist.org/packages/dflydev/fig-cookies
[4]: https://packagist.org/packages/psr/http-factory
[5]: https://packagist.org/packages/psr/http-message
[6]: https://packagist.org/packages/psr/http-server-handler
