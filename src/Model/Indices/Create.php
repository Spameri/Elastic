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


	public function execute(
		string $index
	) : array
	{
		try {
			/** @var array $result */
			$result = $this->clientProvider->client()->indices()->create(
				(
					new \Spameri\ElasticQuery\Document(
						$index
					)
				)->toArray()
			);

			return $result;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
