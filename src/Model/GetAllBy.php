<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class GetAllBy
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;
	/**
	 * @var \Spameri\ElasticQuery\Response\ResultMapper
	 */
	private $resultMapper;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Spameri\ElasticQuery\Response\ResultMapper $resultMapper
	)
	{
		$this->clientProvider = $clientProvider;
		$this->resultMapper = $resultMapper;
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $options
		, string $index
		, ?string $type = NULL
	) : \Spameri\ElasticQuery\Response\ResultSearch
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			$result = $this->clientProvider->client()->search(
				(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain($options->toArray()),
					$type
				)
				)
					->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSearchResults($result);
	}

}
