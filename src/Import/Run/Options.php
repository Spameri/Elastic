<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Run;

readonly class Options
{

	public function __construct(
		private int $lockDuration,
	)
	{
	}


	public function lockDuration(): int
	{
		return $this->lockDuration;
	}

}
