<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class Close
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function execute(
		string $index,
	): bool
	{
		try {
			$result = $this->clientProvider->client()->indices()->close(
				(
					new \Spameri\ElasticQuery\Document($index)
				)->toArray(),
			)
			;

			if ($result['acknowledged'] === true) {
				return true;
			}

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return false;
	}

}
