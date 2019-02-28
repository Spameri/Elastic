<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Indices;


class Close
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
	) : bool
	{
		try {
			$result = $this->clientProvider->client()->indices()->close(
				(
					new \Spameri\ElasticQuery\Document($index)
				)->toArray()
			);

			if ($result['acknowledged'] === TRUE) {
				return TRUE;
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return FALSE;
	}

}
