<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Scroll
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $index,
	): \Spameri\ElasticQuery\Response\ResultSearch
	{
		try {
			if ($elasticQuery->options()->scrollId() === null) {
				$result = $this->clientProvider->client()->search(
					(
						new \Spameri\ElasticQuery\Document(
							$index,
							new \Spameri\ElasticQuery\Document\Body\Plain($elasticQuery->toArray()),
							null,
							[
								'scroll' => $elasticQuery->options()->scroll(),
							],
						)
					)
						->toArray(),
				)->asArray()
				;

				if (isset($result['_scroll_id'])) {
					$elasticQuery->options()->scrollInitialized($result['_scroll_id']);

				} else {
					throw new \Spameri\Elastic\Exception\ScrollNotInitialized(
						'ElasticSearch did not return scroll id.',
					);
				}

			} else {
				$result = $this->clientProvider->client()->scroll(
					(
						new \Spameri\ElasticQuery\Document(
							null,
							null,
							null,
							[
								'scroll' => $elasticQuery->options()->scroll(),
								'scroll_id' => $elasticQuery->options()->scrollId(),
							],
						)
					)
						->toArray(),
				)->asArray()
				;
			}

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSearchResults($result);
	}


	public function closeScroll(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
	): void
	{
		try {
			$this->clientProvider->client()->clearScroll(
				(
					new \Spameri\ElasticQuery\Document(
						null,
						null,
						null,
						[
							'scroll_id' => $elasticQuery->options()->scrollId(),
						],
					)
				)
					->toArray(),
			)
			;

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
