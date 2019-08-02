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
    new App(...)
));

$http->start();
```

[1]: https://packagist.org/packages/slim/psr7
[2]: https://packagist.org/packages/slim/slim
