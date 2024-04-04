<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class InsertMultiple
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray,
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
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
		foreach ($entityCollection as $entity) {
			$entityArray = $this->prepareEntityArray->prepare($entity);
			unset($entityArray['id']);

			$documentsArray[] = [
				'index' => [
					'_index' => $index,
					'_type' => $type,
				],
			];
			$documentsArray[] = $entityArray;
		}

		$document = new \Spameri\ElasticQuery\Document\Bulk($documentsArray);

		try {
			$response = $this->clientProvider->client()->bulk($document->toArray());

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
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
		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapBulkResult($response);
	}

}
