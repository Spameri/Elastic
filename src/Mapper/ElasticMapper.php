<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapper;


class ElasticMapper
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	/**
	 * @var \Spameri\Elastic\Provider\DateTimeProvider
	 */
	private $dateTimeProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->dateTimeProvider = $dateTimeProvider;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function createMapping(
		array $entity,
		string $indexName
	) : void
	{
		try {
			$this->clientProvider->client()->indices()->putMapping(
				(
					new \Spameri\ElasticQuery\Document(
						$indexName,
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'dynamic' => $entity['dynamic'],
							'properties' => $entity['properties'],
						]),
						$entity['index']
					)
				)->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\IndexAlreadyExists
	 */
	public function createIndex(array $entity) : void
	{
		try {
			try {
				$this->clientProvider->client()->indices()->get(
					(
					new \Spameri\ElasticQuery\Document(
						$entity['index']
					)
					)->toArray()
				);

				throw new \Spameri\Elastic\Exception\IndexAlreadyExists($entity['index']);

			} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {
				$indexName = $entity['index'] . '-' . $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);
				$this->clientProvider->client()->indices()->create(
					(
					new \Spameri\ElasticQuery\Document(
						$indexName
					)
					)->toArray()
				);
				$this->createMapping($entity, $indexName);
				$this->addAlias($indexName, $entity['index']);
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}


	public function deleteIndex(
		string $indexName
	) : void
	{
		try {
			try {
				/** @var array $indexes */
				$indexes = $this->clientProvider->client()->indices()->get(
					(
						new \Spameri\ElasticQuery\Document(
							$indexName
						)
					)->toArray()
				);

				if ($indexes) {
					foreach ($indexes as $index) {
						$this->clientProvider->client()->indices()->delete(
							[
								'index' => $index['settings']['index']['provided_name'],
							]
						);
					}
				}

			} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {

			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function addAlias(
		string $index,
		string $alias
	) : void
	{
		try {
			try {
				$this->clientProvider->client()->indices()->get(
					(
						new \Spameri\ElasticQuery\Document(
							$alias
						)
					)->toArray()
				);

				throw new \Spameri\Elastic\Exception\AliasAlreadyExists($alias);

			} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $exception) {
				$this->clientProvider->client()->indices()->putAlias(
					(
						new \Spameri\ElasticQuery\Document(
							$index,
							new \Spameri\ElasticQuery\Document\Body\Plain([
								'actions' => [
									'add' => [
										'index' => $index,
										'alias' => $alias,
									],
								],
							]),
							NULL,
							NULL,
							[
								'name' => $index,
							]
						)
					)->toArray()
				);
			}

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function removeAlias(
		string $index,
		string $alias
	) : void
	{
		try {
			$this->clientProvider->client()->indices()->putAlias(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'actions' => [
								'remove' => [
									'index' => $index,
									'alias' => $alias,
								],
							],
						]),
						NULL,
						NULL,
						[
							'name' => $index,
						]
					)
				)->toArray()
			);

		} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}
	}

}
