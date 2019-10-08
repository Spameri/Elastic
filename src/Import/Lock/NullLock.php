<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Lock;

class NullLock implements \Spameri\Elastic\Import\LockInterface
{

	public function setRunName(string $runName): void
	{
		// do nothing
	}


	public function acquire(int $time): \Spameri\Elastic\Import\LockInterface
	{
		return $this;
	}


	public function release(): void
	{
		// do nothing
	}


	public function extend(int $time): void
	{
		// do nothing
	}

}
