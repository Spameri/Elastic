<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class EntityClassNotResolved extends \Spameri\Elastic\Exception\ElasticSearchException
{

	public function __construct(
		string $indexName,
		int $code = 0,
		\Throwable $previous = NULL
	)
	{
		$message = 'Entity class not resolved for index: ' . $indexName;

		parent::__construct($message, $code, $previous);
	}

}
