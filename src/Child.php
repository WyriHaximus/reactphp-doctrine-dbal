<?php declare(strict_types=1);

namespace WyriHaximus\React\Doctrine\DataBaseAbstractionLayer;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use React\EventLoop\LoopInterface;
use function React\Promise\resolve;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class Child implements ChildInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        new self($messenger, $loop);
    }

    /**
     * @param Messenger $messenger
     * @param LoopInterface $loop
     */
    private function __construct(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('connect', function (Payload $payload) {
            $this->connection = DriverManager::getConnection(
                $payload['connection_config'],
                new Configuration()
            );
        });
        $messenger->registerRpc('fetchAll', function (Payload $payload) {
            return resolve(['all' => $this->connection->fetchAll(
                $payload['query'],
                $payload['params'],
                $payload['types']
            )]);
        });
        $messenger->registerRpc('fetchColumn', function (Payload $payload) {
            return resolve(['column' => $this->connection->fetchColumn(
                $payload['query'],
                $payload['params'],
                $payload['column']
            )]);
        });
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}
