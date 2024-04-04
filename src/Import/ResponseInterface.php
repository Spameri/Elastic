<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface ResponseInterface
{

	public function __construct(
		mixed $response,
		\Spameri\Elastic\Entity\AbstractImport $entity,
	);


	public function isSuccessful(): bool;


	public function getResponse(): mixed;


	public function getEntity(): \Spameri\Elastic\Entity\AbstractImport;

}
