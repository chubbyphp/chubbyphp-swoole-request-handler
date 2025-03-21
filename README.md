# chubbyphp-swoole-request-handler

[![CI](https://github.com/chubbyphp/chubbyphp-swoole-request-handler/actions/workflows/ci.yml/badge.svg)](https://github.com/chubbyphp/chubbyphp-swoole-request-handler/actions/workflows/ci.yml)
[![Coverage Status](https://coveralls.io/repos/github/chubbyphp/chubbyphp-swoole-request-handler/badge.svg?branch=master)](https://coveralls.io/github/chubbyphp/chubbyphp-swoole-request-handler?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchubbyphp%2Fchubbyphp-swoole-request-handler%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/chubbyphp/chubbyphp-swoole-request-handler/master)
[![Latest Stable Version](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/v)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
[![Total Downloads](https://poser.pugx.org/chubbyphp/chubbyphp-swoole-request-handler/downloads)](https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler)
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

 * php: ^8.2
 * [ext-swoole][2]: ^5.1.7|^6.0
 * [dflydev/fig-cookies][3]: ^3.1
 * [psr/http-factory][4]: ^1.1
 * [psr/http-message][5]: ^1.1|^2.0
 * [psr/http-server-handler][6]: ^1.0.2
 * [psr/log][7]: ^2.0|^3.0.2

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-swoole-request-handler][1].

```sh
composer require chubbyphp/chubbyphp-swoole-request-handler "^1.5"
```

## Usage

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
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

2025 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler
[2]: https://www.swoole.co.uk
[3]: https://packagist.org/packages/dflydev/fig-cookies
[4]: https://packagist.org/packages/psr/http-factory
[5]: https://packagist.org/packages/psr/http-message
[6]: https://packagist.org/packages/psr/http-server-handler
[7]: https://packagist.org/packages/psr/log
