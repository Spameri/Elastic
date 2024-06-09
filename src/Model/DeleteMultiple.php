<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class DeleteMultiple
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\ElasticEntityCollectionInterface $entityCollection,
		string $index,
	): \Spameri\ElasticQuery\Response\ResultBulk
	{
		$documentsArray = [];
		/** @var \Spameri\Elastic\Entity\ElasticEntityInterface $entity */
		foreach ($entityCollection as $entity) {
			$documentsArray[] = [
				'delete' => [
					'_index' => $index,
					'_id' => $entity->id()->value(),
				],
			];
		}

		if (\count($documentsArray)) {
			$document = new \Spameri\ElasticQuery\Document\Bulk($documentsArray);

			try {
				$response = $this->clientProvider->client()->bulk($document->toArray())->asArray();

			} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
				throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
			}

			try {
				$this->clientProvider->client()->indices()->refresh(
					(
						new \Spameri\ElasticQuery\Document($index)
					)
						->toArray(),
				)
				;

			} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
				throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
			}

			return $this->resultMapper->mapBulkResult($response);
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}

}
