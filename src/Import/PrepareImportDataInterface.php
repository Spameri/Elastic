<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface PrepareImportDataInterface
{

	public function prepare(
		$entityData
	): \Spameri\Elastic\Entity\AbstractImport;

}
