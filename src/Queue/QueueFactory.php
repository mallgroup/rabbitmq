<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Queue;

use Mallgroup\RabbitMQ\Connection\ConnectionFactory;
use Mallgroup\RabbitMQ\Connection\Exception\ConnectionFactoryException;
use Mallgroup\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueFactory
{

	/**
	 * @var IQueue[]
	 */
	private array $queues = [];


	public function __construct(
		private QueuesDataBag $queuesDataBag,
		private ConnectionFactory $connectionFactory,
		private QueueDeclarator $queueDeclarator
	) {
	}


	/**
	 * @throws QueueFactoryException
	 */
	public function getQueue(string $name): IQueue
	{
		if (!isset($this->queues[$name])) {
			$this->queues[$name] = $this->create($name);
		}

		return $this->queues[$name];
	}


	/**
	 * @throws QueueFactoryException
	 * @throws ConnectionFactoryException
	 */
	private function create(string $name): IQueue
	{
		try {
			$queueData = $this->queuesDataBag->getDataByKey($name);
		} catch (\InvalidArgumentException) {
			throw new QueueFactoryException("Queue [$name] does not exist");
		}

		// (ConnectionFactoryException)
		$connection = $this->connectionFactory->getConnection($queueData['connection']);

		if ($queueData['autoCreate'] === 1) {
			$this->queueDeclarator->declareQueue($name);
		}

		return new Queue(
			$name,
			$connection
		);
	}
}
