<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ;

use Mallgroup\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Mallgroup\RabbitMQ\Producer\IProducer;
use Mallgroup\RabbitMQ\Producer\ProducerFactory;

/**
 * This package uses composer library bunny/bunny. For more information,
 * @see https://github.com/jakubkulhan/bunny
 */
final class Client
{

	public function __construct(private ProducerFactory $producerFactory)
	{
	}


	/**
	 * @throws ProducerFactoryException
	 */
	public function getProducer(string $name): IProducer
	{
		return $this->producerFactory->getProducer($name);
	}
}
