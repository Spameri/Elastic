<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Delete
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		\Spameri\Elastic\Entity\Property\IElasticId $id,
		string $index
	) : bool
	{
		$response = $this->clientProvider->client()->delete(
			(
				new \Spameri\ElasticQuery\Document(
					$index,
					NULL,
					$index,
					$id->value()
				)
			)
			->toArray()
		);

		$this->clientProvider->client()->indices()->refresh(
			(
			new \Spameri\ElasticQuery\Document($index)
			)
				->toArray()
		);

		return $response['result'] === 'deleted';
	}

}
