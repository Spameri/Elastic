<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class ConflictingMapping extends \Spameri\Elastic\Exception\ElasticSearchException
{

	public function __construct(
		string $indexName,
		?\Throwable $previous = NULL
	)
	{
		$message = '';
		if ($previous) {
			$message = $previous->getMessage();
		}

		parent::__construct(
			'You are probably trying to change field mapping of existing field in index or aliased index with name: '
			. $indexName . "\n"
			. 'You need to create new index try spameri:elastic:create-index -f' . "\n"
			. 'ElasticSearch exception message: ' . "\n"
			. $message
			, 0
			, $previous
		);
	}

}
