<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class DocumentNotFound extends \RuntimeException
{

	public function __construct($message)
	{
		parent::__construct('Document "' . $message . '" not found.');
	}
}
