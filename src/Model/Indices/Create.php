<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class Create
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
	 * @param array<mixed> $parameters
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $parameters
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($parameters)
					)
				)->toArray()
			)
				;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
