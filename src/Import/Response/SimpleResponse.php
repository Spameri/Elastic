<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Response;

readonly class SimpleResponse implements \Spameri\Elastic\Import\ResponseInterface
{

	public function __construct(
		private mixed $response,
		private \Spameri\Elastic\Entity\AbstractImport $entity,
	)
	{
	}


	public function isSuccessful(): bool
	{
		return (bool) $this->response;
	}


	public function getResponse(): mixed
	{
		return $this->response;
	}


	public function getEntity(): \Spameri\Elastic\Entity\AbstractImport
	{
		return $this->entity;
	}

}
