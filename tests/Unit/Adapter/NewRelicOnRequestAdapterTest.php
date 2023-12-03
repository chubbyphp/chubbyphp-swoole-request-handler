<?php

declare(strict_types=1);

namespace Chubbyphp\SwooleRequestHandler\Adapter
{
    final class TestNewRelicStartTransaction
    {
        /**
         * @var array<int, array>
         */
        private static array $calls = [];

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
        private static array $calls = [];

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
    final class NewRelicOnRequestAdapterTest extends TestCase
    {
        use MockByCallsTrait;

        public function testInvoke(): void
        {
            TestNewRelicStartTransaction::reset();
            TestNewRelicEndTransaction::reset();

            /** @var MockObject|SwooleRequest $swooleRequest */
            $swooleRequest = $this->getMockByCalls(SwooleRequest::class);

            /** @var MockObject|SwooleResponse $swooleResponse */
            $swooleResponse = $this->getMockByCalls(SwooleResponse::class);

            /** @var MockObject|OnRequestInterface $onRequest */
            $onRequest = $this->getMockByCalls(OnRequestInterface::class, [
                Call::create('__invoke')->with($swooleRequest, $swooleResponse),
            ]);

            $adapter = new NewRelicOnRequestAdapter($onRequest, 'myapp');
            $adapter($swooleRequest, $swooleResponse);

            self::assertSame([['appname' => 'myapp', 'license' => null]], TestNewRelicStartTransaction::all());
            self::assertSame([['ignore' => false]], TestNewRelicEndTransaction::all());
        }
    }
}
