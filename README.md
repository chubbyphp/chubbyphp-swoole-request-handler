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

A request handler adapter for [swoole][2], using PSR-7, PSR-15 and PSR-17.

It turns a [swoole][2] HTTP `Server` into a runtime for any [PSR-15][8] request handler: each incoming
swoole request is converted into a [PSR-7][9] server request through [PSR-17][10] factories, handed to your
application, and the returned PSR-7 response is sent back through the swoole response.

Because swoole is a long-running process, your application is bootstrapped once per worker process and then
serves many requests. This removes the per-request bootstrap cost of a classic PHP-FPM setup.

## Requirements

 * php: ^8.3
 * [ext-swoole][2]: ^5.1.8|^6.1.9
 * [dflydev/fig-cookies][3]: ^3.2
 * [psr/http-factory][4]: ^1.1
 * [psr/http-message][5]: ^1.1|^2.0
 * [psr/http-server-handler][6]: ^1.0.2
 * [psr/log][7]: ^2.0|^3.0.2

## Suggest

Any [PSR-7][9] / [PSR-17][10] implementation can be used, for example:

 * [guzzlehttp/psr7][11] (with [http-interop/http-factory-guzzle][12])
 * [laminas/laminas-diactoros][13]
 * [nyholm/psr7][14]
 * [slim/psr7][15]
 * [sunrise/http-message][16]

## Installation

Through [Composer](http://getcomposer.org) as [chubbyphp/chubbyphp-swoole-request-handler][1].

```sh
composer require chubbyphp/chubbyphp-swoole-request-handler "^1.6"
```

The examples below use [slim/psr7][15] as the PSR-7 / PSR-17 implementation:

```sh
composer require slim/psr7 "^1.8"
```

## Usage

### Basic server

Create a `server.php` and start it with `php server.php`:

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Swoole\Http\Server;

require __DIR__.'/vendor/autoload.php';

/** @var RequestHandlerInterface $app */
$app = ...; // your PSR-15 application, bootstrapped once per worker process

$http = new Server('0.0.0.0', 8080);

// number of worker processes, typically the number of CPU cores
$http->set(['worker_num' => 4]);

$http->on('start', static function (Server $server): void {
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

### Components

The package consists of three small, replaceable parts. Each one has an interface, so you can swap in your own
implementation where the defaults don't fit.

| Class                    | Interface                         | Responsibility                                                                                              |
|--------------------------|-----------------------------------|-------------------------------------------------------------------------------------------------------------|
| `OnRequest`              | `OnRequestInterface`              | Swoole `request` callback: builds the PSR-7 request, calls the PSR-15 handler and emits the response.      |
| `PsrRequestFactory`      | `PsrRequestFactoryInterface`      | Converts a swoole request into a PSR-7 server request via the given PSR-17 factories.                       |
| `SwooleResponseEmitter`  | `SwooleResponseEmitterInterface`  | Converts a PSR-7 response into a swoole response and sends it to the client.                                |

`PsrRequestFactory` maps the following data:

 * method, URI and all headers
 * cookies, query params and the parsed body (form data)
 * uploaded files, including nested ones (as `UploadedFileInterface` instances)
 * the raw body, written to the request body stream
 * server params from swoole's server array, with upper-cased keys (`REQUEST_METHOD`, `REMOTE_ADDR`, ...)

`SwooleResponseEmitter` maps the following data:

 * status code and reason phrase
 * all headers, with `Set-Cookie` headers translated to swoole cookies (including `SameSite`)
 * the body, streamed in chunks of 128 KiB by default; pass another chunk size to the constructor to change it

### Long-running process caveats

Swoole keeps the PHP process alive between requests, which differs from PHP-FPM:

 * Superglobals like `$_GET`, `$_POST`, `$_SERVER` or `$_COOKIE` are not populated. Read everything from the
   PSR-7 request instead.
 * Anything you keep in static properties or in long-lived services persists across requests. Avoid request
   specific state in shared objects, or reset it per request.
 * `REMOTE_ADDR` is the address of the direct peer. If you run behind a reverse proxy,
   use a middleware such as [chubbyphp/chubbyphp-trusted-proxy][17] to resolve the client IP from headers.

### With Blackfire

`BlackfireOnRequestAdapter` wraps an `OnRequestInterface` and profiles a request whenever the
`X-Blackfire-Query` header is present, for example when triggered by the Blackfire browser extension or the
`blackfire curl` command. Requests without that header pass through untouched. The probe is always ended,
even if the wrapped handler throws.

Requires the `blackfire` extension and the [blackfire/php-sdk][18] package.

```php
<?php

declare(strict_types=1);

namespace App;

use Blackfire\Client;
use Blackfire\Profile\Configuration;
use Chubbyphp\SwooleRequestHandler\Adapter\BlackfireOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
use Psr\Log\LoggerInterface;

/** @var OnRequestInterface $onRequest */
$onRequest = ...;

/** @var LoggerInterface $logger */
$logger = ...;

if (extension_loaded('blackfire')) {
    $onRequest = new BlackfireOnRequestAdapter(
        $onRequest,
        new Client(),
        new Configuration(), // optional, defaults to new Configuration()
        $logger              // optional, defaults to a NullLogger; receives Blackfire client errors
    );
}

$http->on('request', $onRequest);
```

### With New Relic

`NewRelicOnRequestAdapter` wraps an `OnRequestInterface` and starts a New Relic transaction for every request.
The transaction is always ended, even if the wrapped handler throws, so each request is reported separately
instead of as one endless transaction per worker process.

Requires the `newrelic` extension.

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\Adapter\NewRelicOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequestInterface;

/** @var OnRequestInterface $onRequest */
$onRequest = ...;

if (extension_loaded('newrelic') && false !== $appname = ini_get('newrelic.appname')) {
    $onRequest = new NewRelicOnRequestAdapter($onRequest, $appname);
}

$http->on('request', $onRequest);
```

Both adapters implement `OnRequestInterface`, so they can be combined by nesting them.

## Copyright

2026 Dominik Zogg

[1]: https://packagist.org/packages/chubbyphp/chubbyphp-swoole-request-handler
[2]: https://www.swoole.co.uk
[3]: https://packagist.org/packages/dflydev/fig-cookies
[4]: https://packagist.org/packages/psr/http-factory
[5]: https://packagist.org/packages/psr/http-message
[6]: https://packagist.org/packages/psr/http-server-handler
[7]: https://packagist.org/packages/psr/log
[8]: https://www.php-fig.org/psr/psr-15
[9]: https://www.php-fig.org/psr/psr-7
[10]: https://www.php-fig.org/psr/psr-17
[11]: https://packagist.org/packages/guzzlehttp/psr7
[12]: https://packagist.org/packages/http-interop/http-factory-guzzle
[13]: https://packagist.org/packages/laminas/laminas-diactoros
[14]: https://packagist.org/packages/nyholm/psr7
[15]: https://packagist.org/packages/slim/psr7
[16]: https://packagist.org/packages/sunrise/http-message
[17]: https://packagist.org/packages/chubbyphp/chubbyphp-trusted-proxy
[18]: https://packagist.org/packages/blackfire/php-sdk
