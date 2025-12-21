<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class GetByTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_getby_test';


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


	public function testQueryWithTermFilter(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', $id),
		);

		$result = $getBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(1, $result->stats()->total());
	}


	public function testQueryWithMatchAllFilter(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		// Create multiple entities
		for ($i = 0; $i < 3; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$insert->execute($entity, self::INDEX, false);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\MatchAll(),
		);

		$result = $getBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(3, $result->stats()->total());
	}


	public function testQueryWithBoolMustNot(): void
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

		$id1 = $insert->execute($entity1, self::INDEX, false);
		$insert->execute($entity2, self::INDEX, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		// Get all except the first one
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustNotQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', $id1),
		);

		$result = $getBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(1, $result->stats()->total());
	}


	public function testQueryWithNoResults(): void
	{
		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\Term('_id', 'nonexistent-id'),
		);

		$result = $getBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(0, $result->stats()->total());
	}


	public function testQueryReturnsResultSearchWithHits(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\MatchAll(),
		);

		$result = $getBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::count(1, $result->hits());
		$hits = \iterator_to_array($result->hits());
		\Tester\Assert::same($id, $hits[0]->id());
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

(new GetByTest())->run();
