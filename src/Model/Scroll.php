<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Scroll
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


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
		, string $index
		, ?string $type = NULL
	) : \Spameri\ElasticQuery\Response\ResultSearch
	{
		if ($type === NULL) {
			$type = $index;
		}

		try {
			if ($elasticQuery->options()->scrollId() === NULL) {
				$result = $this->clientProvider->client()->search(
					(
						new \Spameri\ElasticQuery\Document(
							$index,
							new \Spameri\ElasticQuery\Document\Body\Plain($elasticQuery->toArray()),
							$type,
							NULL,
							[
								'scroll' => $elasticQuery->options()->scroll(),
							]
						)
					)
						->toArray()
				);

				if (isset($result['_scroll_id'])) {
					$elasticQuery->options()->scrollInitialized($result['_scroll_id']);

				} else {
					throw new \Spameri\Elastic\Exception\ScrollNotInitialized(
						'ElasticSearch did not return scroll id.'
					);
				}

			} else {
				$result = $this->clientProvider->client()->scroll(
					(
						new \Spameri\ElasticQuery\Document(
							NULL,
							NULL,
							NULL,
							NULL,
							[
								'scroll' => $elasticQuery->options()->scroll(),
								'scroll_id' => $elasticQuery->options()->scrollId(),
							]
						)
					)
						->toArray()
				);
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSearchResults($result);
	}


	public function closeScroll(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery
	) : void
	{
		try {
			$this->clientProvider->client()->clearScroll(
				(
					new \Spameri\ElasticQuery\Document(
						NULL,
						NULL,
						NULL,
						NULL,
						[
							'scroll_id' => $elasticQuery->options()->scrollId(),
						]
					)
				)
					->toArray()
			);


		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
