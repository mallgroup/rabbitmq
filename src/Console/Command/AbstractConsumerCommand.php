<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Console\Command;

use Mallgroup\RabbitMQ\Consumer\ConsumerFactory;
use Mallgroup\RabbitMQ\Consumer\ConsumersDataBag;
use Mallgroup\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractConsumerCommand extends Command
{

	public function __construct(protected ConsumersDataBag $consumersDataBag, protected ConsumerFactory $consumerFactory)
	{
		parent::__construct();
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	protected function validateConsumerName(string $consumerName): void
	{
		try {
			$this->consumerFactory->getConsumer($consumerName);
		} catch (ConsumerFactoryException $e) {
			throw new \InvalidArgumentException(
				sprintf(
					"%s\n\n Available consumers: %s",
					$e->getMessage(),
					implode(
						'',
						array_map(static fn ($s): string => "\n\t- [{$s}]", $this->consumersDataBag->getDataKeys())
					)
				)
			);
		}
	}
}
