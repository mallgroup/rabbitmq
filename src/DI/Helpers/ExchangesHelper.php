<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\DI\Helpers;

use Mallgroup\RabbitMQ\AbstractDataBag;
use Mallgroup\RabbitMQ\Exchange\ExchangeDeclarator;
use Mallgroup\RabbitMQ\Exchange\ExchangeFactory;
use Mallgroup\RabbitMQ\Exchange\ExchangesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ExchangesHelper extends AbstractHelper
{

	public const ExchangeTypes = ['direct', 'topic', 'headers', 'fanout', 'x-delayed-message'];

	public function getConfigSchema(): Schema
	{
		return Expect::arrayOf(
			Expect::structure([
				'connection' => Expect::string('default'),
				'type' => Expect::anyOf(...self::ExchangeTypes)->default(self::ExchangeTypes[0]),
				'passive' => Expect::bool(false),
				'durable' => Expect::bool(true),
				'autoDelete' => Expect::bool(false),
				'internal' => Expect::bool(false),
				'noWait' => Expect::bool(false),
				'arguments' => Expect::array(),
				'queueBindings' => Expect::arrayOf(
					Expect::structure([
						'routingKey' => Expect::anyOf(
							Expect::string(),
							Expect::arrayOf(
								Expect::string()
							)
						)->default([''])->castTo('array'),
						'noWait' => Expect::bool(false),
						'arguments' => Expect::array(),
					])->castTo('array'),
					'string'
				)->default([]),
				'federation' => Expect::structure([
					'uri' => Expect::string()->required()->dynamic(),
					'prefetchCount' => Expect::int(20)->min(1),
					'reconnectDelay' => Expect::int(1)->min(1),
					'messageTTL' => Expect::int(),
					'expires' => Expect::int(),
					'ackMode' => Expect::anyOf(...self::AckTypes)->default(self::AckTypes[0]),
					'policy' => Expect::structure([
						'priority' => Expect::int(0),
						'arguments' => Expect::arrayOf(
							Expect::anyOf(Expect::string(), Expect::int(), Expect::bool()),
							'string'
						)->default([])->before(fn(array $args): array => $this->normalizePolicyArguments($args)),
					])->castTo('array'),
				])->castTo('array')->required(false),
				'autoCreate' => Expect::int(
					AbstractDataBag::AutoCreateLazy
				)->before(
					fn(mixed $input): int => $this->normalizeAutoDeclare($input)
				),
			])->castTo('array'),
			'string'
		);
	}


	/**
	 * @param array<string, mixed> $config
	 * @throws \InvalidArgumentException
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$exchangesDataBag = $builder
			->addDefinition($this->extension->prefix('exchangesDataBag'))
			->setFactory(ExchangesDataBag::class)
			->setArguments([$config]);

		$builder
			->addDefinition($this->extension->prefix('exchangesDeclarator'))
			->setFactory(ExchangeDeclarator::class);

		return $builder
			->addDefinition($this->extension->prefix('exchangeFactory'))
			->setFactory(ExchangeFactory::class)
			->setArguments([$exchangesDataBag]);
	}

	/**
	 * @param array<string, mixed> $arguments
	 * @return array<string, mixed>
	 */
	protected function normalizePolicyArguments(array $arguments = []): array
	{
		$return = [];
		foreach ($arguments as $key => $value) {
			$return[$this->normalizePolicyArgumentKey($key)] = $value;
		}

		return $return;
	}

	private function normalizePolicyArgumentKey(string $key): string
	{
		return strtolower((string)preg_replace(['/([a-z\d])([A-Z])/', '/([^-])([A-Z][a-z])/'], '$1-$2', $key));
	}
}
