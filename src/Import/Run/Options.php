<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Run;

class Options
{

	/**
	 * @var int
	 */
	private $lockDuration;


	public function __construct(
		int $lockDuration,
	)
	{
		$this->lockDuration = $lockDuration;
	}


	public function lockDuration(): int
	{
		return $this->lockDuration;
	}

}
