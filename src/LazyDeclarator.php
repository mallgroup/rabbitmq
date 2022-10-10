<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Connection\IConnection;
use Contributte\RabbitMQ\Exchange\ExchangeDeclarator;
use Contributte\RabbitMQ\Exchange\ExchangesDataBag;
use Contributte\RabbitMQ\Queue\QueueDeclarator;
use Contributte\RabbitMQ\Queue\QueuesDataBag;
use Exception;

final class LazyDeclarator
{
	public function __construct(
		private QueuesDataBag $queuesDataBag,
		private ExchangesDataBag $exchangesDataBag,
		private QueueDeclarator $queueDeclarator,
		private ExchangeDeclarator $exchangeDeclarator,
		private ConnectionFactory $connectionFactory,
	) {
	}

	/**
	 * @throws Exception
	 */
	public function declare(): void
	{
		array_map(
			static fn (IConnection $connection) => $connection->resetChannel(),
			$this->connectionFactory->getConnections()
		);
		$this->declareQueues($this->queuesDataBag->getDataKeys());
		$this->declareExchanges($this->exchangesDataBag->getDataKeys());
	}

	/**
	 * @param string[] $queues
	 * @throws Exception
	 */
	private function declareQueues(array $queues): void
	{
		foreach ($queues as $queue) {
			$config = $this->queuesDataBag->getDataByKey($queue);
			if ($config['autoCreate'] !== AbstractDataBag::AutoCreateLazy) {
				continue;
			}
			$this->queueDeclarator->declareQueue($queue);
		}
	}

	/**
	 * @param string[] $exchanges
	 * @throws Exception
	 */
	private function declareExchanges(array $exchanges): void
	{
		foreach ($exchanges as $exchange) {
			$config = $this->exchangesDataBag->getDataByKey($exchange);
			if ($config['autoCreate'] !== AbstractDataBag::AutoCreateLazy) {
				continue;
			}
			$this->exchangeDeclarator->declareExchange($exchange);
		}
	}
}
