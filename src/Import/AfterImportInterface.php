<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface AfterImportInterface
{

	public function process(
		$entityData,
		\Spameri\Elastic\Import\ResponseInterface $result
	);

}
