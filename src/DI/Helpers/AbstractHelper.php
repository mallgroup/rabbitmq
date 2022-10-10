<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\DI\Helpers;

use Mallgroup\RabbitMQ\AbstractDataBag;
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
