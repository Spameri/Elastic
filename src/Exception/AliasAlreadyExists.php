<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class AliasAlreadyExists extends \Spameri\Elastic\Exception\AbstractElasticSearchException
{

	public function __construct(
		string $indexName
	)
	{
		parent::__construct(
			'You are trying to create already existing alias with name: ' . $indexName . "\n"
		);
	}

}
