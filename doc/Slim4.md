# Slim 4

## Requirements

 * [slim/psr7][1]: ^0.4
 * [slim/slim][2]: ^4.0

## Example

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Swoole\Http\Server;

$loader = require __DIR__.'/vendor/autoload.php';

/** @var App $app*/
$app = ...;

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
    $app
));

$http->start();
```

### with newrelic

```php
<?php

declare(strict_types=1);

namespace App;

use Chubbyphp\SwooleRequestHandler\NewRelicOnRequestAdapter;
use Chubbyphp\SwooleRequestHandler\OnRequest;

/** @var OnRequest $onRequest */
$onRequest = ...;

$http->on('request', new NewRelicOnRequestAdapter(ini_get('newrelic.appname'), $onRequest);
```

[1]: https://packagist.org/packages/slim/psr7
[2]: https://packagist.org/packages/slim/slim
