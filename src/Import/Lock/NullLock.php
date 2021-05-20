<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Lock;

class NullLock implements \Spameri\Elastic\Import\LockInterface
{

	// phpcs:disable
	public function setRunName(string $runName): void
	{
		// phpcs:enable
		// do nothing
	}


	// phpcs:disable
	public function acquire(int $time): \Spameri\Elastic\Import\LockInterface
	{
		// phpcs:enable

		return $this;
	}


	public function release(): void
	{
		// do nothing
	}


	// phpcs:disable
	public function extend(int $time): void
	{
		// phpcs:enable
		// do nothing
	}

}
