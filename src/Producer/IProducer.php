<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Producer;

interface IProducer
{
	public const DeliveryModeNonPersistent = 1;
	public const DeliveryModePersistent = 2;

	/**
	 * @param array<string, string|int> $headers
	 */
	public function publish(string $message, array $headers = [], ?string $routingKey = null): void;
	public function addOnPublishCallback(callable $callback): void;
}
