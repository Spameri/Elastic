<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapper;


class ElasticMapper
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	protected $clientProvider;
	/**
	 * @var \Kdyby\Clock\IDateTimeProvider
	 */
	protected $constantProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Kdyby\Clock\IDateTimeProvider $constantProvider
	)
	{
		$this->clientProvider = $clientProvider;
		$this->constantProvider = $constantProvider;
	}


	/**
	 * @param array $entity
	 * @throws \Exception
	 */
	public function createMapping($entity) : void
	{
		$this->clientProvider->client()->indices()->putMapping(
			(
				new \Spameri\ElasticQuery\Document(
					$entity['index'],
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
	 * @throws \Exception
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


	public function deleteIndex(array $entity)
	{
		try {
			/** @var array $indexes */
			$indexes = $this->clientProvider->client()->indices()->get(
				(
					new \Spameri\ElasticQuery\Document(
						$entity['index']
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

		return NULL;
	}
}
