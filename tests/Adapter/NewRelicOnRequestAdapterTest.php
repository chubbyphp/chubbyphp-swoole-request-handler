<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter
{
    final class TestExtesionLoaded
    {
        /**
         * @var array<int, string>
         */
        private static $extensions = [];

        public static function add(string $name): void
        {
            self::$extensions[] = $name;
        }

        public static function isLoaded(string $name): bool
        {
            return in_array($name, self::$extensions, true);
        }

        public static function reset(): void
        {
            self::$extensions = [];
        }
    }

    function extension_loaded(string $name): bool
    {
        return TestExtesionLoaded::isLoaded($name);
    }

    final class TestNewRelicStartTransaction
    {
        /**
         * @var array<int, array>
         */
        private static $calls = [];

        public static function add(string $appname, ?string $license = null): void
        {
            self::$calls[] = ['appname' => $appname, 'license' => $license];
        }

        /**
         * @return array<int, array>
         */
        public static function all(): array
        {
            return self::$calls;
        }

        public static function reset(): void
        {
            self::$calls = [];
        }
    }

    function newrelic_start_transaction(string $appname, ?string $license = null): void
    {
        TestNewRelicStartTransaction::add($appname, $license);
    }

    final class TestNewRelicEndTransaction
    {
        /**
         * @var array<int, array>
         */
        private static $calls = [];

        public static function add(bool $ignore): void
        {
            self::$calls[] = ['ignore' => $ignore];
        }

        /**
         * @return array<int, array>
         */
        public static function all(): array
        {
            return self::$calls;
        }

        public static function reset(): void
        {
            self::$calls = [];
        }
    }

    function newrelic_end_transaction(bool $ignore = false): void
    {
        TestNewRelicEndTransaction::add($ignore);
    }
}

namespace Chubbyphp\Tests\SwooleRequestHandler\Unit\Adapter
{
    use Chubbyphp\Mock\Call;
    use Chubbyphp\Mock\MockByCallsTrait;
    use Chubbyphp\SwooleRequestHandler\Adapter\NewRelicOnRequestAdapter;
    use Chubbyphp\SwooleRequestHandler\Adapter\TestExtesionLoaded;
    use Chubbyphp\SwooleRequestHandler\Adapter\TestNewRelicEndTransaction;
    use Chubbyphp\SwooleRequestHandler\Adapter\TestNewRelicStartTransaction;
    use Chubbyphp\SwooleRequestHandler\OnRequestInterface;
    use PHPUnit\Framework\TestCase;
    use PHPUnit\SwooleRequestHandler\MockObject\MockObject;
    use Swoole\Http\Request as SwooleRequest;
    use Swoole\Http\Response as SwooleResponse;

    /**
     * @covers \Chubbyphp\SwooleRequestHandler\Adapter\NewRelicOnRequestAdapter
     *
     * @internal
     */
    final class NewRelicRouteMiddlewareTest extends TestCase
    {
        use MockByCallsTrait;

        public function testWithoutNewRelicExtension(): void
        {
            TestExtesionLoaded::reset();
            TestNewRelicStartTransaction::reset();
            TestNewRelicEndTransaction::reset();

            /** @var SwooleRequest|MockObject $swooleRequest */
            $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

            /** @var SwooleResponse|MockObject $swooleResponse */
            $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

            /** @var OnRequestInterface|MockObject $onRequest */
            $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
                Call::create('__invoke')->with($swooleRequest, $swooleResponse),
            ]);

            $adapter = new NewRelicOnRequestAdapter('myapp', $onRequest);
            $adapter($swooleRequest, $swooleResponse);

            self::assertSame([], TestNewRelicStartTransaction::all());
            self::assertSame([], TestNewRelicEndTransaction::all());
        }

        public function testWithNewRelicExtension(): void
        {
            TestExtesionLoaded::reset();
            TestNewRelicStartTransaction::reset();
            TestNewRelicEndTransaction::reset();

            TestExtesionLoaded::add('newrelic');

            /** @var SwooleRequest|MockObject $swooleRequest */
            $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

            /** @var SwooleResponse|MockObject $swooleResponse */
            $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

            /** @var OnRequestInterface|MockObject $onRequest */
            $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
                Call::create('__invoke')->with($swooleRequest, $swooleResponse),
            ]);

            $adapter = new NewRelicOnRequestAdapter('myapp', $onRequest);
            $adapter($swooleRequest, $swooleResponse);

            self::assertSame([['appname' => 'myapp', 'license' => null]], TestNewRelicStartTransaction::all());
            self::assertSame([['ignore' => false]], TestNewRelicEndTransaction::all());
        }
    }
}
