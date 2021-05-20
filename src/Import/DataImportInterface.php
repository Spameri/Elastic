<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface DataImportInterface
{

	public function import(
		\Spameri\Elastic\Entity\AbstractImport $entity
	): \Spameri\Elastic\Import\ResponseInterface;

}
