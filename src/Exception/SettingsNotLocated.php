<?php declare(strict_types = 1);

namespace Spameri\Elastic\Exception;


class SettingsNotLocated extends \Spameri\Elastic\Exception\ElasticSearchException
{

	public function __construct(
		string $indexName
	)
	{
		parent::__construct('Settings not found for index name ' . $indexName);
	}

}
