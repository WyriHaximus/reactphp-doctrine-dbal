<?php declare(strict_types=1);

namespace WyriHaximus\React\Doctrine\DataBaseAbstractionLayer;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\ChildProcess\Pool\WorkerInterface;

final class Pool
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param LoopInterface $loop
     * @param array $credentials
     *
     * @return PromiseInterface
     */
    public static function create(LoopInterface $loop, array $credentials): PromiseInterface
    {
        return Flexible::createFromClass(Child::class, $loop)->then(function (PoolInterface $pool) use ($credentials) {
            return new self($pool, $credentials);
        });
    }

    /**
     * @param PoolInterface $pool
     * @param array $credentials
     *
     * @internal
     */
    private function __construct(PoolInterface $pool, array $credentials)
    {
        $this->pool = $pool;
        $this->pool->on('worker', function (WorkerInterface $worker) use ($credentials) {
            $worker->rpc(MessageFactory::rpc(
                'connect',
                [
                    'connection_config' => $credentials,
                ]
            ));
        });
    }

    /**
     * @param string $query
     * @param array $params
     * @param array $types
     *
     * @return PromiseInterface
     */
    public function fetchAll(string $query, array $params = [], array $types = []): PromiseInterface
    {
        return $this->pool->rpc(MessageFactory::rpc(
            'fetchAll',
            [
                'query' => $query,
                'params' => $params,
                'types' => $types,
            ]
        ))->then(function (Payload $payload) {
            return $payload->getPayload()['all'];
        });
    }

    /**
     * @param string $query
     * @param array $params
     * @param int $column
     *
     * @return PromiseInterface
     */
    public function fetchColumn(string $query, array $params = [], int $column = 0): PromiseInterface
    {
        return $this->pool->rpc(MessageFactory::rpc(
            'fetchColumn',
            [
                'query' => $query,
                'params' => $params,
                'column' => $column,
            ]
        ))->then(function (Payload $payload) {
            return $payload->getPayload()['column'];
        });
    }
}
