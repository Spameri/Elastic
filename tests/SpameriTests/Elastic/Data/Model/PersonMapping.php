<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class PersonMapping extends \Spameri\Elastic\Settings\AbstractIndexConfig
{

	public function provide(): \Spameri\ElasticQuery\Mapping\Settings
	{
		return new \Spameri\ElasticQuery\Mapping\Settings($this->index);
	}

}
