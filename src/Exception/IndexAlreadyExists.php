<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;

class IndexAlreadyExists extends \Spameri\Elastic\Exception\AbstractElasticSearchException
{

	public function __construct(
		string $indexName,
	)
	{
		parent::__construct(
			'You are trying to create already existing index or aliased index with name: ' . $indexName . "\n"
			. 'You can delete already existing index by -f option' . "\n",
		);
	}

}
