<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface PrepareImportDataInterface
{

	public function prepare(
		mixed $entityData,
	): \Spameri\Elastic\Entity\AbstractImport;

}
