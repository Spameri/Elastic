<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Insert
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray,
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
		string $index,
		bool $hasSti = FALSE,
	): string
	{
		$entityArray = $this->prepareEntityArray->prepare($entity, $hasSti);
		unset($entityArray['id']);

		try {
			$response = $this->clientProvider->client()->index(
				(
					new \Spameri\ElasticQuery\Document(
						index: $index,
						body: new \Spameri\ElasticQuery\Document\Body\Plain($entityArray),
						id: $entity->id()->value(),
					)
				)->toArray(),
			)->asArray()
			;

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

		if (isset($response['result']) && ($response['result'] === 'created' || $response['result'] === 'updated')) {
			$entity->id = new \Spameri\Elastic\Entity\Property\ElasticId($response['_id']);
			return $response['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}

}
