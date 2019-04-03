<?php declare(strict_types = 1);

namespace Spameri\ElasticQuery\Exception;


class ResponseCouldNotBeMapped extends \InvalidArgumentException
{

	public function __construct(
		$message,
		int $code = 0,
		\Throwable $previous = NULL
	)
	{
		parent::__construct(json_encode($message), $code, $previous);
	}

}
