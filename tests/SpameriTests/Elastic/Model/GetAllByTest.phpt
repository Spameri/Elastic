<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class GetAllByTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_getallby_test';


	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => self::INDEX . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testMultipleResultsWithPagination(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		// Create 10 entities
		for ($i = 0; $i < 10; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$insert->execute($entity, self::INDEX, false);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Get first page (5 items)
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(5);
		$elasticQuery->options()->changeFrom(0);

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(10, $result->stats()->total()); // Total in index
		\Tester\Assert::count(5, $result->hits()); // Returned in this page
	}


	public function testPaginationWithOffset(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		// Create 10 entities
		for ($i = 0; $i < 10; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$insert->execute($entity, self::INDEX, false);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Get second page (skip first 5, get next 5)
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(5);
		$elasticQuery->options()->changeFrom(5);

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(10, $result->stats()->total());
		\Tester\Assert::count(5, $result->hits());
	}


	public function testPaginationBeyondResults(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		// Create 5 entities
		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$insert->execute($entity, self::INDEX, false);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Request beyond available results
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->changeFrom(10);

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(5, $result->stats()->total());
		\Tester\Assert::count(0, $result->hits()); // No hits past the end
	}


	public function testEmptyResults(): void
	{
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', 'nonexistent'),
		);

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(0, $result->stats()->total());
		\Tester\Assert::count(0, $result->hits());
	}


	public function testSortingById(): void
	{
		// Skip: Sorting by _id field is disabled in ES 8+ for security reasons
		// To enable: indices.id_field_data.enabled=true (not recommended for production)
		\Tester\Environment::skip('Sorting by _id is disabled in ES 8+ by default');

		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$ids = [];
		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$ids[] = $insert->execute($entity, self::INDEX, false);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Sort ascending by _id
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->sort()->add(new \Spameri\ElasticQuery\Options\Sort('_id', \Spameri\ElasticQuery\Options\Sort::ASC));

		$resultAsc = $getAllBy->execute($elasticQuery, self::INDEX);

		// Sort descending by _id
		$elasticQueryDesc = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQueryDesc->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQueryDesc->options()->sort()->add(new \Spameri\ElasticQuery\Options\Sort('_id', \Spameri\ElasticQuery\Options\Sort::DESC));

		$resultDesc = $getAllBy->execute($elasticQueryDesc, self::INDEX);

		// First result of asc should be different from first result of desc
		$ascIds = $resultAsc->hits()->ids();
		$descIds = $resultDesc->hits()->ids();
		\Tester\Assert::notSame($ascIds[0], $descIds[0]);

		// Last of asc should equal first of desc
		\Tester\Assert::same(
			$ascIds[\count($ascIds) - 1],
			$descIds[0],
		);
	}


	public function testComplexQuery(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$entity3 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id1 = $insert->execute($entity1, self::INDEX, false);
		$id2 = $insert->execute($entity2, self::INDEX, false);
		$id3 = $insert->execute($entity3, self::INDEX, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Complex query: must match one of two IDs using Terms, must not match third
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addFilter(new \Spameri\ElasticQuery\Query\Terms('_id', [$id1, $id2]));
		$elasticQuery->addMustNotQuery(new \Spameri\ElasticQuery\Query\Term('_id', $id3));

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(2, $result->stats()->total());

		$foundIds = $result->hits()->ids();
		\Tester\Assert::contains($id1, $foundIds);
		\Tester\Assert::contains($id2, $foundIds);
		\Tester\Assert::notContains($id3, $foundIds);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new GetAllByTest())->run();
