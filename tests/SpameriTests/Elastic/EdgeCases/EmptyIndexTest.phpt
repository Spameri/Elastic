<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EdgeCases;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for operations on empty indexes.
 * @testCase
 */
class EmptyIndexTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_EDGE_CASE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testFindAllOnEmptyIndexReturnsEmptyCollection(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$result = $entityManager->findAll(
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\ElasticEntityCollection::class, $result);
		\Tester\Assert::same(0, $result->count());
	}


	public function testFindByOnEmptyIndexReturnsEmptyCollection(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $entityManager->findBy(
			$query,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\ElasticEntityCollection::class, $result);
		\Tester\Assert::same(0, $result->count());
	}


	public function testFindOneByOnEmptyIndexThrowsException(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\Term('name', 'nonexistent'));

		\Tester\Assert::exception(
			static fn () => $entityManager->findOneBy(
				$query,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testFindByIdOnEmptyIndexThrowsException(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		\Tester\Assert::exception(
			static fn () => $entityManager->find(
				'nonexistent-id-12345',
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testRemoveNonExistentDocumentThrowsException(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create an entity with a non-existent ID
		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId('nonexistent-id-12345'),
			'test',
			'content',
		);

		// Attempting to delete a non-existent document throws an exception
		\Tester\Assert::exception(
			static fn () => $entityManager->remove($entity),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testGetAllByOnEmptyIndexReturnsEmptyResultSet(): void
	{
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $getAllBy->execute(
			$query,
			\SpameriTests\Elastic\Config::INDEX_EDGE_CASE,
		);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(0, $result->stats()->total());
	}


	public function testSearchOnEmptyIndexReturnsEmptyResults(): void
	{
		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $search->execute(
			$query,
			\SpameriTests\Elastic\Config::INDEX_EDGE_CASE,
		);

		\Tester\Assert::same(0, $result->stats()->total());
	}


	public function testAggregateOnEmptyIndexReturnsZeroBuckets(): void
	{
		/** @var \Spameri\Elastic\Model\Aggregate $aggregate */
		$aggregate = $this->container->getByType(\Spameri\Elastic\Model\Aggregate::class);

		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$leafCollection = new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
			'names',
			null,
			new \Spameri\ElasticQuery\Aggregation\Term('name'),
		);
		$query->addAggregation($leafCollection);

		$result = $aggregate->execute(
			$query,
			\SpameriTests\Elastic\Config::INDEX_EDGE_CASE,
		);

		$namesAgg = $result->aggregations()->getAggregation('names');
		\Tester\Assert::notNull($namesAgg);
		\Tester\Assert::same(0, \iterator_count($namesAgg->buckets()));
	}


	public function testIndexExistsReturnsTrue(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Exists $exists */
		$exists = $this->container->getByType(\Spameri\Elastic\Model\Indices\Exists::class);

		$result = $exists->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE);

		\Tester\Assert::true($result);
	}


	public function testGetMappingOnEmptyIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\GetMapping $getMapping */
		$getMapping = $this->container->getByType(\Spameri\Elastic\Model\Indices\GetMapping::class);

		$result = $getMapping->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE);

		\Tester\Assert::type('array', $result);
		\Tester\Assert::true(\array_key_exists(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE, $result));
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
	}

}

(new EmptyIndexTest())->run();
