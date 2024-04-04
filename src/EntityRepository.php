<?php

declare(strict_types = 1);

namespace Spameri\Elastic;

readonly class EntityRepository
{

	public function __construct(
		private \Spameri\Elastic\Model\Insert $insert,
		private \Spameri\Elastic\Model\GetAllBy $getAllBy,
		private \Spameri\Elastic\Model\Delete $delete,
		private \Spameri\Elastic\Model\EntitySettingsLocator $entitySettingsLocator,
		private \Spameri\Elastic\Factory\EntityFactory $entityFactory,
	)
	{
	}

	public function find(
		string $id,
		string $class,
	): \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term(
				'_id',
				$id,
			),
		);

		return $this->findOneBy($elasticQuery, $class);
	}


	public function findOneBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	): \Spameri\Elastic\Entity\ElasticEntityInterface
	{
		$elasticQuery->options()->changeSize(1);

		$entities = $this->findBy($elasticQuery, $class);

		if (\count($entities) === 0) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound('Entity ' . $class . ' not found.');
		}

		return \reset($entities);
	}


	public function findBy(
		\Spameri\ElasticQuery\ElasticQuery $elasticQuery,
		string $class,
	): array
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($class);

		try {
			$resultSearch = $this->getAllBy->execute($elasticQuery, $indexConfig->indexName());

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);

			throw $exception;
		}

		$entities = [];
		foreach ($resultSearch->hits() as $hit) {
			try {
				$entities[] = $this->entityFactory->create($hit, $class)->current();

			} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
				\Tracy\Debugger::log($exception->getMessage(), \Tracy\ILogger::CRITICAL);
			}
		}

		// TODO univerzální kolekce
		return $entities;
	}


	public function findAll(string $class): array
	{
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(10000);

		return $this->findBy($elasticQuery, $class);
	}


	public function persist(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity
	): string
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($entity::class);

		return $this->insert->execute(
			entity: $entity,
			index: $indexConfig->indexName(),
			hasSti: $indexConfig->hasSti(),
		);
	}


	public function remove(\Spameri\Elastic\Entity\ElasticEntityInterface $entity): bool
	{
		$indexConfig = $this->entitySettingsLocator->locateByEntityClass($entity::class);

		return $this->delete->execute($entity->id(), $indexConfig->indexName());
	}

}
