# Zend Expressive

## Requirements

* [zendframework/zend-expressive][1]: ^3.2.1

## Example

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

[1]: https://packagist.org/packages/zendframework/zend-expressive
