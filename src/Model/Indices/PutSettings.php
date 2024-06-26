<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;

readonly class PutSettings
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @param array<mixed> $settings
	 * @return array<mixed>
	 */
	public function execute(
		string $index,
		array $settings,
	): array
	{
		try {
			return $this->clientProvider->client()->indices()->putSettings(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($settings),
					)
				)->toArray(),
			)->asArray()
				;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
