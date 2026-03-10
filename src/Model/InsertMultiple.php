<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class InsertMultiple
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray,
		private \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	/**
	 * @param \Spameri\Elastic\Entity\ElasticEntityCollectionInterface<\Spameri\Elastic\Entity\AbstractElasticEntity> $entityCollection
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\ElasticEntityCollectionInterface $entityCollection,
		string $index,
	): \Spameri\ElasticQuery\Response\ResultBulk
	{
		// Handle empty collection - return empty result without calling ElasticSearch
		if ($entityCollection->count() === 0) {
			return new \Spameri\ElasticQuery\Response\ResultBulk(
				new \Spameri\ElasticQuery\Response\Stats(0, false, 0),
				new \Spameri\ElasticQuery\Response\Result\BulkActionCollection(),
			);
		}

		$documentsArray = [];
		foreach ($entityCollection as $entity) {
			$entityArray = $this->prepareEntityArray->prepare($entity);
			unset($entityArray['id']);

			$documentsArray[] = [
				'index' => [
					'_index' => $index,
				],
			];
			$documentsArray[] = $entityArray;
		}

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

		$responseArray = $response->asArray();

		// Manually create ResultBulk because ResultMapper::mapBulkResult has a bug
		// (it calls mapStats() which expects 'hits' key that bulk responses don't have)
		$bulkActions = [];
		foreach ($responseArray['items'] ?? [] as $item) {
			$actionType = \array_key_first($item);
			$action = $item[$actionType];
			$shards = $action['_shards'] ?? ['total' => 0, 'successful' => 0, 'skipped' => 0, 'failed' => 0];
			$bulkActions[] = new \Spameri\ElasticQuery\Response\Result\BulkAction(
				action: $actionType,
				index: $action['_index'],
				type: $action['_type'] ?? '_doc',
				id: $action['_id'],
				version: $action['_version'] ?? 0,
				result: $action['result'] ?? '',
				shards: new \Spameri\ElasticQuery\Response\Shards(
					$shards['total'] ?? 0,
					$shards['successful'] ?? 0,
					$shards['skipped'] ?? 0,
					$shards['failed'] ?? 0,
				),
				status: $action['status'],
				seqNo: $action['_seq_no'] ?? 0,
				primaryTerm: $action['_primary_term'] ?? 0,
			);
		}

		$bulkActionCollection = new \Spameri\ElasticQuery\Response\Result\BulkActionCollection(...$bulkActions);
		$stats = new \Spameri\ElasticQuery\Response\Stats(
			$responseArray['took'] ?? 0,
			$responseArray['errors'] ?? false,
			\count($responseArray['items'] ?? []),
		);

		return new \Spameri\ElasticQuery\Response\ResultBulk($stats, $bulkActionCollection);
	}

}
