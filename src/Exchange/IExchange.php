<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Exchange;

use Mallgroup\RabbitMQ\Connection\IConnection;

interface IExchange
{

	public function getName(): string;

	/**
	 * @return QueueBinding[]
	 */
	public function getQueueBindings(): array;


	public function getConnection(): IConnection;
}
