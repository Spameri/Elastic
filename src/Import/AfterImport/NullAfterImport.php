<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\AfterImport;

class NullAfterImport implements \Spameri\Elastic\Import\AfterImportInterface
{

	public function process(
		array $entityData,
		\Spameri\Elastic\Import\ResponseInterface $result
	): void
	{
		// do nothing
	}

}
