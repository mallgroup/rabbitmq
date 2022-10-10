<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Exchange;

use Mallgroup\RabbitMQ\Connection\ConnectionFactory;
use Mallgroup\RabbitMQ\Exchange\Exception\ExchangeFactoryException;
use Mallgroup\RabbitMQ\Queue\QueueFactory;
use Exception;

final class ExchangeDeclarator
{
	public function __construct(
		private ConnectionFactory $connectionFactory,
		private ExchangesDataBag $exchangesDataBag,
		private QueueFactory $queueFactory
	) {
	}

	/**
	 * @throws Exception
	 */
	public function declareExchange(string $name): void
	{
		try {
			$exchangeData = $this->exchangesDataBag->getDataByKey($name);
		} catch (\InvalidArgumentException) {
			throw new ExchangeFactoryException("Exchange [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($exchangeData['connection']);

		$connection->getChannel()->exchangeDeclare(
			$name,
			$exchangeData['type'],
			$exchangeData['passive'],
			$exchangeData['durable'],
			$exchangeData['autoDelete'],
			$exchangeData['internal'],
			$exchangeData['noWait'],
			$exchangeData['arguments']
		);

		if ($exchangeData['queueBindings'] !== []) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				$queue = $this->queueFactory->getQueue($queueName);
				foreach ($queueBinding['routingKey'] as $routingKey) {
					$connection->getChannel()->queueBind(
						$queue->getName(),
						$name,
						$routingKey,
						$queueBinding['noWait'],
						$queueBinding['arguments']
					);
				}
			}
		}

		if (isset($exchangeData['federation'])) {
			try {
				$api = $this->connectionFactory->getApi($exchangeData['connection']);
				$federation = $exchangeData['federation'];

				$api->createFederation(
					$name,
					$connection->getVhost(),
					$federation['uri'],
					$federation['prefetchCount'],
					$federation['reconnectDelay'],
					$federation['messageTTL'],
					$federation['expires'],
					$federation['ackMode'],
					$federation['policy']
				);
			} catch (\RuntimeException $e) {
				throw new ExchangeFactoryException("Failed to create federated exchange [$name]", (int) $e->getCode(), $e);
			}
		}
	}
}
