<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class PutMapping
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
		string $index
		, array $mapping
		, string $dynamic = 'false'
		, ?string $type = NULL
	) : array
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			/** @var array $result */
			$result = $this->clientProvider->client()->indices()->putMapping(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'properties' => $mapping,
							'dynamic' => $dynamic,
						]),
						$type
					)
				)->toArray()
			);

			return $result;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
