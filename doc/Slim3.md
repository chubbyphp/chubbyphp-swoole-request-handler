# Slim 3

## Requirements

 * [http-interop/http-factory-slim][1]: ^2.0
 * [slim/slim][2]: ^3.12.1

## Example

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
    new class() implements RequestHandlerInterface
    {
        public function __construct()
        {
            $this->app = new App();
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

[1]: https://packagist.org/packages/http-interop/http-factory-slim
[2]: https://packagist.org/packages/slim/slim
