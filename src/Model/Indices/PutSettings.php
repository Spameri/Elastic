<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class PutSettings
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
		, array $settings
	) : array
	{
		try {
			/** @var array $result */
			$result = $this->clientProvider->client()->indices()->putSettings(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain($settings)
					)
				)->toArray()
			);

			return $result;

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
