<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Queue;

use Mallgroup\RabbitMQ\Connection\IConnection;

interface IQueue
{

	public function getName(): string;


	public function getConnection(): IConnection;
}
