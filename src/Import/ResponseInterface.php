<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface ResponseInterface
{

	/**
	 * @param mixed $response
	 * @param \Spameri\Elastic\Entity\AbstractImport $entity
	 */
	public function __construct(
		$response,
		\Spameri\Elastic\Entity\AbstractImport $entity
	);

	public function isSuccessful(): bool;


	/**
	 * @return mixed
	 */
	public function getResponse();

	public function getEntity(\Spameri\Elastic\Entity\AbstractImport $entity): \Spameri\Elastic\Entity\AbstractImport;

}
