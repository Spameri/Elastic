<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapper;


class ElasticMapper
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	/**
	 * @var \Kdyby\DateTimeProvider\Provider\ConstantProvider
	 */
	private $constantProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Kdyby\DateTimeProvider\Provider\ConstantProvider $constantProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->constantProvider = $constantProvider;
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	public function createMapping(
		array $entity,
		string $indexName
	) : void
	{
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
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 * @throws \Spameri\Elastic\Exception\IndexAlreadyExists
	 */
	public function createIndex(array $entity) : void
	{
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
			$indexName = $entity['index'] . '-' . $this->constantProvider->getDateTime()->format('Y-m-d_H-i-s');
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
	}


	public function deleteIndex(
		string $indexName
	): void
	{
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
	}

	public function addAlias(
		string $index,
		string $alias
	) : void
	{
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
	}


	public function removeAlias(
		string $index,
		string $alias
	) : void
	{
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
	}

}
