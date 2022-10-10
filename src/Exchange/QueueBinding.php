<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Exchange;

use Mallgroup\RabbitMQ\Queue\IQueue;

final class QueueBinding
{

	/**
	 * @param string[] $routingKey
	 */
	public function __construct(
		private IQueue $queue,
		private array $routingKey
	) {
	}


	public function getQueue(): IQueue
	{
		return $this->queue;
	}


	/**
	 * @return string[]
	 */
	public function getRoutingKey(): array
	{
		return $this->routingKey;
	}
}
