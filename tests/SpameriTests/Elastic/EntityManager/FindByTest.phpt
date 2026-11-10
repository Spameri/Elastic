<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class FindByTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_TITLE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_TITLE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testFindByReturnsElasticEntityCollection(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\MatchAll(),
		);

		$collection = $entityManager->findBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\ElasticEntityCollection::class, $collection);
		\Tester\Assert::same(1, $collection->count());
	}


	public function testFindByWithPagination(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create 5 entities
		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$entityManager->persist($entity);
		}

		// Wait for ES to index
		\usleep(500000);

		// Test with limit
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(2);

		$collection = $entityManager->findBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::same(2, $collection->count());
	}


	public function testFindByWithOffset(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create 5 entities
		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$entityManager->persist($entity);
		}

		// Wait for ES to index
		\usleep(500000);

		// Test with offset
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->changeFrom(3);

		$collection = $entityManager->findBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Should have 2 results (5 - 3 offset)
		\Tester\Assert::same(2, $collection->count());
	}


	public function testFindByEmptyResultReturnsEmptyCollection(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Query with no matches
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', 'nonexistent-id'),
		);

		$collection = $entityManager->findBy($elasticQuery, \SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\ElasticEntityCollection::class, $collection);
		\Tester\Assert::same(0, $collection->count());
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_TITLE);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new FindByTest())->run();
