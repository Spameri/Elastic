<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface PrepareImportDataInterface
{

	/**
	 * @param mixed $entityData
	 */
	public function prepare(
		$entityData,
	): \Spameri\Elastic\Entity\AbstractImport;

}
