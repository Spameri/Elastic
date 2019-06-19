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


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\Elastic\Entity\Property\IElasticId $id
		, string $index
		, ?string $type = NULL
	) : bool
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			$response = $this->clientProvider->client()->delete(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					NULL,
					$type,
					$id->value()
				)
				)
					->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		try {
			$this->clientProvider->client()->indices()->refresh(
				(
				new \Spameri\ElasticQuery\Document($index)
				)
					->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $response['result'] === 'deleted';
	}

}
