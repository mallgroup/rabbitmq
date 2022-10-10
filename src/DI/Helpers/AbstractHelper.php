<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\AbstractDataBag;
use Nette\DI\CompilerExtension;
use Nette\Schema\Schema;

abstract class AbstractHelper
{
	public const AckTypes = ['on-confirm', 'on-publish', 'no-ack'];

	public function __construct(
		protected CompilerExtension $extension
	) {
	}


	abstract public function getConfigSchema(): Schema;

	protected function normalizeAutoDeclare(mixed $value): int
	{
		return match ($value) {
			'lazy' => AbstractDataBag::AutoCreateLazy,
			'never' => AbstractDataBag::AutoCreateNever,
			default => (int) $value ? 1 : 0,
		};
	}
}
