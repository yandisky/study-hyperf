<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Amqp\IO;

use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use PhpAmqpLib\Wire\IO\AbstractIO;
use Swoole\Coroutine\Socket;

class SwooleIO extends AbstractIO
{
    public const READ_BUFFER_WAIT_INTERVAL = 100000;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $connectionTimeout;

    /**
     * @var int
     */
    protected $readWriteTimeout;

    /**
     * @var bool
     */
    protected $openSSL;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var null|Socket
     */
    private $sock;

    /**
     * @throws \InvalidArgumentException when readWriteTimeout argument does not 2x the heartbeat
     */
    public function __construct(
        string $host,
        int $port,
        int $connectionTimeout,
        int $readWriteTimeout = 3,
        bool $openSSL = false
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->connectionTimeout = $connectionTimeout;
        $this->readWriteTimeout = $readWriteTimeout;
        $this->openSSL = $openSSL;
    }

    /**
     * Sets up the stream connection.
     *
     * @throws AMQPRuntimeException
     */
    public function connect()
    {
        $this->sock = $this->makeClient();
    }

    public function read($len)
    {
        $data = $this->sock->recvAll($len, $this->readWriteTimeout);
        if ($data === false || strlen($data) !== $len) {
            throw new AMQPConnectionClosedException('Read data failed, The reason is ' . $this->sock->errMsg);
        }

        return $data;
    }

    public function write($data)
    {
        $len = $this->sock->sendAll($data, $this->readWriteTimeout);

        /* @phpstan-ignore-next-line */
        if ($data === false || strlen($data) !== $len) {
            throw new AMQPConnectionClosedException('Send data failed, The reason is ' . $this->sock->errMsg);
        }
    }

    public function check_heartbeat()
    {
    }

    public function close()
    {
        $this->sock && $this->sock->close();
    }

    public function select($sec, $usec = 0)
    {
        return 1;
    }

    public function disableHeartbeat()
    {
        return $this;
    }

    public function reenableHeartbeat()
    {
        return $this;
    }

    protected function makeClient()
    {
        $sock = new Socket(AF_INET, SOCK_STREAM, 0);

        if ($this->openSSL === true) {
            $sock->setProtocol(['open_ssl' => true]);
        }

        if (! $sock->connect($this->host, $this->port, $this->connectionTimeout)) {
            throw new AMQPRuntimeException(
                sprintf('Error Connecting to server: %s ', $sock->errMsg),
                $sock->errCode
            );
        }
        return $sock;
    }

    protected function write_heartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $this->write($pkt->getvalue());
    }

    protected function do_select($sec, $usec)
    {
        return 1;
    }
}
