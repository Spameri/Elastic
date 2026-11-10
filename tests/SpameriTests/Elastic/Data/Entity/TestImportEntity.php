<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Simple import entity for testing import system.
 */
readonly class TestImportEntity extends \Spameri\Elastic\Entity\AbstractImport
{

	public function __construct(
		int|string $key,
		public string $name,
		public int $value,
	)
	{
		parent::__construct($key);
	}


	public function key(): int|string
	{
		return parent::key();
	}

}
