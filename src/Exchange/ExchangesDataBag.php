<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Exchange;

use Mallgroup\RabbitMQ\AbstractDataBag;

final class ExchangesDataBag extends AbstractDataBag
{

	/**
	 * @param array<string, mixed> $config
	 */
	public function addExchangeConfig(string $exchangeName, array $config): void
	{
		$this->data[$exchangeName] = $config;
	}
}
