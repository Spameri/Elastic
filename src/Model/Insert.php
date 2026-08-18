<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Insert
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray,
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\Elastic\Model\IdentityMap $identityMap,
	)
	{
	}


	/**
	 * New entities are indexed in two phases so that nested entities can reference
	 * the parent (and circular references do not loop):
	 *
	 *  1. the parent is indexed first (empty body, no refresh) to obtain its id and
	 *     is marked as "persisting" in the identity map,
	 *  2. its nested entities are persisted - a back-reference to the parent now
	 *     resolves to the parent's id instead of re-persisting it,
	 *  3. the parent is re-indexed with the full body.
	 *
	 * Entities that already have an id are indexed once, as before.
	 *
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
		string $index,
		bool $hasSti = false,
	): string
	{
		// Only check isChanged for entities with real IDs (updates)
		// New entities (EmptyElasticId) should always be inserted
		$hasRealId = ! $entity->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId;
		if ($hasRealId && $this->identityMap->isChanged($entity) === false) {
			return $entity->id()->value();
		}

		// New entity: index it first (shallow body) to obtain its id before its
		// nested entities are persisted, so circular references can resolve to it.
		if ($hasRealId === false) {
			$this->index($entity, $index, $this->shallowBody($entity), false);
		}

		$this->identityMap->markInserted($entity);
		$this->identityMap->markPersisting($entity);

		try {
			$entityArray = $this->prepareEntityArray->prepare($entity, $hasSti);
			unset($entityArray['id']);

			$id = $this->index($entity, $index, $entityArray, true);
			$this->identityMap->markInserted($entity);

			return $id;

		} finally {
			$this->identityMap->unmarkPersisting($entity);
		}
	}


	/**
	 * @param array<mixed> $entityArray
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	private function index(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
		string $index,
		array $entityArray,
		bool $refresh,
	): string
	{
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

		if ($refresh === true) {
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
		}

		if (isset($response['result']) && ($response['result'] === 'created' || $response['result'] === 'updated')) {
			$entity->id = new \Spameri\Elastic\Entity\Property\ElasticId($response['_id']);

			return $response['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}


	/**
	 * Minimal non-empty body for the first index of a new entity: every field
	 * (except the id) nulled. The real values are written by the second index;
	 * this only reserves the document so it gets a server generated id.
	 *
	 * @return array<string, null>
	 */
	private function shallowBody(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): array
	{
		$body = [];
		foreach (\array_keys($entity->entityVariables()) as $key) {
			if ($key === 'id') {
				continue;
			}

			$body[$key] = null;
		}

		return $body;
	}

}
