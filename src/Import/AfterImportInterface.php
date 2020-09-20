<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface AfterImportInterface
{

	/**
	 * @param mixed $entityData
	 */
	public function process(
		$entityData,
		\Spameri\Elastic\Import\ResponseInterface $result
	): void;

}
