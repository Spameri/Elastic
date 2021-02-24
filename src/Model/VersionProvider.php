<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class VersionProvider
{

	/**
	 * @var int
	 */
	private static $versionNumber = \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_24;


	public function __construct(
		int $versionNumber
	)
	{
		self::$versionNumber = $versionNumber;
	}


	public static function provide(): int
	{
		return self::$versionNumber;
	}

}
