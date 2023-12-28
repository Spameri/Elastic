<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

class Exists
{

	private \Spameri\Elastic\ClientProvider $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		string $index,
	): bool
	{
		try {
			return $this->clientProvider->client()->indices()->exists(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
					)
				)->toArray(),
			)
			;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
