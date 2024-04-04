<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class DeleteMultiple
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
		private VersionProvider $versionProvider,
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
		string|null $type = NULL,
	): \Spameri\ElasticQuery\Response\ResultBulk
	{
		if ($type === NULL) {
			$type = $index;
		}

		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$type = '_doc';
		}

		$documentsArray = [];
		/** @var \Spameri\Elastic\Entity\ElasticEntityInterface $entity */
		foreach ($entityCollection as $entity) {
			$documentsArray[] = [
				'delete' => [
					'_index' => $index,
					'_type' => $type,
					'_id' => $entity->id()->value(),
				],
			];
		}

		if (\count($documentsArray)) {
			$document = new \Spameri\ElasticQuery\Document\Bulk($documentsArray);

			try {
				$response = $this->clientProvider->client()->bulk($document->toArray());

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
