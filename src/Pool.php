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

    public static function create(LoopInterface $loop, array $credentials): PromiseInterface
    {
        return Flexible::createFromClass(Child::class, $loop)->then(function (PoolInterface $pool) use ($credentials) {
            return new self($pool, $credentials);
        });
    }

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
}
