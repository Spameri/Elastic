<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Lock;

class FileLock implements \Spameri\Elastic\Import\LockInterface
{

	private string $runName;


	public function __construct(
		private readonly string $lockDir,
	)
	{
	}


	public function setRunName(string $runName): void
	{
		$this->runName = $runName;
	}


	public function acquire(int $time): \Spameri\Elastic\Import\LockInterface
	{
		$lockReleaseTime = \file_get_contents($this->getFileName());
		if (
			$lockReleaseTime
			&& $lockReleaseTime > \time()
		) {
			throw new \Spameri\Elastic\Import\Exception\Fatal('Already locked.');
		}

		\file_put_contents($this->getFileName(), \time() + $time);

		return $this;
	}


	public function release(): void
	{
		\unlink($this->getFileName());
	}


	public function extend(int $time): void
	{
		\file_put_contents($this->getFileName(), \time() + $time);
	}


	public function getFileName(): string
	{
		return $this->lockDir . \DIRECTORY_SEPARATOR . $this->runName;
	}

}
