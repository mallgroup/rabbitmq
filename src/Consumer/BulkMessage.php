<?php

declare(strict_types=1);

namespace Mallgroup\RabbitMQ\Consumer;

use Bunny\Channel;
use Bunny\Message;

class BulkMessage
{
	public function __construct(private Message $message, private Channel $channel)
	{
	}

	public function getMessage(): Message
	{
		return $this->message;
	}

	public function getChannel(): Channel
	{
		return $this->channel;
	}
}
