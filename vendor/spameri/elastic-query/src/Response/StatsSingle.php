<?php declare(strict_types = 1);

namespace Spameri\ElasticQuery\Response;


class StatsSingle
{

	/**
	 * @var int
	 */
	private $version;
	/**
	 * @var bool
	 */
	private $found;


	public function __construct(
		int $version
		, bool $found
	)
	{
		$this->version = $version;
		$this->found = $found;
	}


	public function version() : int
	{
		return $this->version;
	}


	public function found() : bool
	{
		return $this->found;
	}

}
