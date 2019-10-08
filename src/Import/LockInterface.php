<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface LockInterface
{

	public function setRunName(string $runName): void;

	/**
	 * @throws \Spameri\Elastic\Import\Exception\AlreadyLocked
	 */
	public function acquire(
		int $time
	): self;


	public function release(): void;


	public function extend(
		int $time
	): void;

}
