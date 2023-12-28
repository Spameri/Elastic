<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class VersionProvider
{

	private int $versionNumber;


	public function __construct(
		int $versionNumber = \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7,
	)
	{
		$this->versionNumber = $versionNumber;
	}


	public function provide(): int
	{
		return $this->versionNumber;
	}

}
