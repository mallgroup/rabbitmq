<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Exchange;

use Mallgroup\RabbitMQ\Queue\IQueue;
use function implode;

final class QueueBinding
{

	/**
	 * @param string[] $routingKey
	 */
	public function __construct(private IQueue $queue, private array $routingKey)
	{
	}


	public function getQueue(): IQueue
	{
		return $this->queue;
	}


	public function getRoutingKey(): string
	{
		return implode(' ', $this->routingKey);
	}


	/**
	 * @return string[]
	 */
	public function getRoutingKeys(): array
	{
		return $this->routingKey;
	}
}
