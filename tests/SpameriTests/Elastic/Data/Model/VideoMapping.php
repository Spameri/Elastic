<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class VideoMapping implements \Spameri\Elastic\Settings\IndexConfigInterface
{

	private string $index;


	public function __construct(
		string $index
	)
	{
		$this->index = $index;
	}


	public function provide(): \Spameri\ElasticQuery\Mapping\Settings
	{
		return new \Spameri\ElasticQuery\Mapping\Settings($this->index);
	}

}
