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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\MessageInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use Psr\Container\ContainerInterface;

class Builder
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $poolFactory;

    /**
     * @var ConnectionFactory
     */
    protected $factory;

    public function __construct(ContainerInterface $container, ConnectionFactory $factory)
    {
        $this->container = $container;
        $this->factory = $factory;
    }

    /**
     * @throws AMQPProtocolChannelException when the channel operation is failed
     */
    public function declare(MessageInterface $message, ?AMQPChannel $channel = null): void
    {
        $releaseToChannel = false;
        if (! $channel) {
            $connection = $this->factory->getConnection($message->getPoolName());
            $channel = $connection->getChannel();
            $releaseToChannel = true;
        }

        try {
            $builder = $message->getExchangeBuilder();

            $channel->exchange_declare($builder->getExchange(), $builder->getType(), $builder->isPassive(), $builder->isDurable(), $builder->isAutoDelete(), $builder->isInternal(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());
        } catch (\Throwable $exception) {
            if ($releaseToChannel && isset($channel)) {
                $channel->close();
            }

            throw $exception;
        }
        if ($releaseToChannel) {
            isset($connection) && $connection->releaseChannel($channel);
        }
    }
}
