<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface AfterImportInterface
{

	/**
	 * @param array<mixed> $entityData
	 */
	public function process(
		array $entityData,
		\Spameri\Elastic\Import\ResponseInterface $result
	): void;

}
