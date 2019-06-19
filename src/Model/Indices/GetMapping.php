<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class GetMapping
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
		, ?string $type = NULL
	) : array
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			/** @var array $result */
			$result = $this->clientProvider->client()->indices()->getMapping(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						NULL,
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
