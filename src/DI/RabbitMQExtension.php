<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\DI;

use Mallgroup\RabbitMQ\Client;
use Mallgroup\RabbitMQ\Console\Command\ConsumerCommand;
use Mallgroup\RabbitMQ\Console\Command\DeclareQueuesAndExchangesCommand;
use Mallgroup\RabbitMQ\Console\Command\StaticConsumerCommand;
use Mallgroup\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Mallgroup\RabbitMQ\DI\Helpers\ConsumersHelper;
use Mallgroup\RabbitMQ\DI\Helpers\ExchangesHelper;
use Mallgroup\RabbitMQ\DI\Helpers\ProducersHelper;
use Mallgroup\RabbitMQ\DI\Helpers\QueuesHelper;
use Mallgroup\RabbitMQ\LazyDeclarator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\DI\PhpGenerator;
use Nette\PhpGenerator\Literal;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @property-read string $name
 * @property-read Compiler $compiler
 * @property-read Closure $initialization
 */
final class RabbitMQExtension extends CompilerExtension
{
	private ConnectionsHelper $connectionsHelper;
	private QueuesHelper $queuesHelper;
	private ProducersHelper $producersHelper;
	private ExchangesHelper $exchangesHelper;
	private ConsumersHelper $consumersHelper;

	public function __construct()
	{
		$this->connectionsHelper = new ConnectionsHelper($this);
		$this->queuesHelper = new QueuesHelper($this);
		$this->exchangesHelper = new ExchangesHelper($this);
		$this->producersHelper = new ProducersHelper($this);
		$this->consumersHelper = new ConsumersHelper($this);
	}

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'connections' => $this->connectionsHelper->getConfigSchema(),
			'queues' => $this->queuesHelper->getConfigSchema(),
			'consumers' => $this->consumersHelper->getConfigSchema(),
			'exchanges' => $this->exchangesHelper->getConfigSchema(),
			'producers' => $this->producersHelper->getConfigSchema(),
		])->castTo('array');
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$this->processConfig(new PhpGenerator($builder), $config);

		$this->connectionsHelper->setup($builder, $config['connections']);
		$this->queuesHelper->setup($builder, $config['queues']);
		$this->exchangesHelper->setup($builder, $config['exchanges']);
		$this->producersHelper->setup($builder, $config['producers']);
		$this->consumersHelper->setup($builder, $config['consumers']);

		/**
		 * Register Client class
		 */
		$builder->addDefinition($this->prefix('client'))
			->setFactory(Client::class);
		$builder->addDefinition($this->prefix('declarator'))
			->setFactory(LazyDeclarator::class);

		$this->setupConsoleCommand();
	}


	public function setupConsoleCommand(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('console.consumerCommand'))
			->setFactory(ConsumerCommand::class)
			->setTags(['console.command' => 'rabbitmq:consumer']);

		$builder->addDefinition($this->prefix('console.staticConsumerCommand'))
			->setFactory(StaticConsumerCommand::class)
			->setTags(['console.command' => 'rabbitmq:staticConsumer']);

		$builder->addDefinition($this->prefix('console.declareQueuesExchangesCommand'))
			->setFactory(DeclareQueuesAndExchangesCommand::class)
			->setTags(['console.command' => 'rabbitmq:declareQueuesAndExchanges']);
	}

	protected function processConfig(PhpGenerator $generator, mixed &$item): void
	{
		if (is_array($item)) {
			foreach ($item as &$value) {
				$this->processConfig($generator, $value);
			}
		} elseif ($item instanceof Statement) {
			$item = new Literal($generator->formatStatement($item));
		}
	}
}
