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
			$this->clientProvider->client()->indices()->putAlias(
				(
					new \Spameri\ElasticQuery\Document(
						$indexName,
						new \Spameri\ElasticQuery\Document\Body\Plain([
							'actions' => [
								'add' => [
									'index' => $indexName,
									'alias' => $entity['index'],
								],
							],
						]),
						NULL,
						NULL,
						[
							'name' => $entity['index'],
						]
					)
				)->toArray()
			);
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

}
