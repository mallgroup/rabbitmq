<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Queue;

use Mallgroup\RabbitMQ\Connection\ConnectionFactory;
use Mallgroup\RabbitMQ\Queue\Exception\QueueFactoryException;
use Exception;

final class QueueDeclarator
{

	public function __construct(
		private ConnectionFactory $connectionFactory,
		private QueuesDataBag $queuesDataBag
	) {
	}

	/**
	 * @throws Exception
	 */
	public function declareQueue(string $name): void
	{
		try {
			$queueData = $this->queuesDataBag->getDataByKey($name);
		} catch (\InvalidArgumentException) {
			throw new QueueFactoryException("Queue [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($queueData['connection']);

		$connection->getChannel()->queueDeclare(
			$name,
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);
	}
}
