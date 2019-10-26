<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface ResponseInterface
{

	public function __construct(
		$response,
		\Spameri\Elastic\Entity\AbstractImport $entity
	);

	public function isSuccessful(): bool;

	public function getResponse();

	public function getEntity($entity);

}