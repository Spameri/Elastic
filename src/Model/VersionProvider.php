<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class VersionProvider
{

	public function __construct(
		private int $versionNumber = \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7,
	)
	{
	}


	public function provide(): int
	{
		return $this->versionNumber;
	}

}
