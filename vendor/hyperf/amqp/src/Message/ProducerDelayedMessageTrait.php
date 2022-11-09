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
namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Builder\ExchangeBuilder;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * @method ProducerMessage getExchange()
 * @method ProducerMessage getType()
 * @property ProducerMessage $properties
 */
trait ProducerDelayedMessageTrait
{
    /**
     * Set the delay time.
     * @return $this
     */
    public function setDelayMs(int $millisecond, string $name = 'x-delay'): self
    {
        $this->properties['application_headers'] = new AMQPTable([$name => $millisecond]);
        return $this;
    }

    /**
     * Overwrite.
     */
    public function getExchangeBuilder(): ExchangeBuilder
    {
        return (new ExchangeBuilder())->setExchange((string) $this->getExchange())
            ->setType('x-delayed-message')
            ->setArguments(new AMQPTable(['x-delayed-type' => $this->getType()]));
    }
}
