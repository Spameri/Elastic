<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Response;

class SimpleResponse implements \Spameri\Elastic\Import\ResponseInterface
{

	/**
	 * @var mixed
	 */
	private $response;

	/**
	 * @var \Spameri\Elastic\Entity\AbstractImport
	 */
	private $entity;


	public function __construct(
		$response,
		\Spameri\Elastic\Entity\AbstractImport $entity
	)
	{
		$this->response = $response;
		$this->entity = $entity;
	}


	public function isSuccessful(): bool
	{
		return $this->response ? TRUE : FALSE;
	}


	public function getResponse()
	{
		return $this->response;
	}


	public function getEntity($entity)
	{
		return $this->entity;
	}

}
