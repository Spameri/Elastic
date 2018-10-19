<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Get
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
		\Spameri\Elastic\Entity\Property\ElasticId $id
		, string $index
	) : ?array
	{
		$response = $this->clientProvider->client()->get(
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

		$data = NULL;
		if ($response['found']) {
			$data = $response['_source'];
			$data['id'] = $response['_id'];
		}

		return $data;
	}
}
