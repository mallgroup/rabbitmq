<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Console\Command;

use Mallgroup\RabbitMQ\AbstractDataBag;
use Mallgroup\RabbitMQ\Exchange\ExchangeDeclarator;
use Mallgroup\RabbitMQ\Exchange\ExchangesDataBag;
use Mallgroup\RabbitMQ\Queue\QueueDeclarator;
use Mallgroup\RabbitMQ\Queue\QueuesDataBag;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeclareQueuesAndExchangesCommand extends Command
{

	public function __construct(
		private QueuesDataBag $queuesDataBag,
		private QueueDeclarator $queueDeclarator,
		private ExchangesDataBag $exchangesDataBag,
		private ExchangeDeclarator $exchangeDeclarator
	) {
		parent::__construct('rabbitmq:declareQueuesAndExchanges');
	}


	protected function configure(): void
	{
		$this->setDescription(
			'Creates all queues and exchanges defined in configs that you can create. Intended to run during deploy process'
		);
	}


	/**
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('<info>Declaring queues:</info>');

		foreach ($this->queuesDataBag->getDataKeys() as $queueName) {
			if (!$this->isDeclarable($this->queuesDataBag->getDataByKey($queueName))) {
				continue;
			}

			$output->writeln(' - ' . $queueName);
			$this->queueDeclarator->declareQueue($queueName);
		}

		$output->writeln('');
		$output->writeln('<info>Declaring exchanges:</info>');

		foreach ($this->exchangesDataBag->getDataKeys() as $exchangeName) {
			if (!$this->isDeclarable($this->exchangesDataBag->getDataByKey($exchangeName))) {
				continue;
			}

			$output->writeln(' - ' . $exchangeName);
			$this->exchangeDeclarator->declareExchange($exchangeName);
		}

		$output->writeln('');
		$output->writeln('<info>Declarations done!</info>');

		return 0;
	}

	/**
	 * @param array<string, mixed> $config
	 * @return bool
	 */
	protected function isDeclarable(array $config): bool
	{
		return ($config['autoCreate'] ?? AbstractDataBag::AutoCreateLazy) !== AbstractDataBag::AutoCreateNever;
	}
}
