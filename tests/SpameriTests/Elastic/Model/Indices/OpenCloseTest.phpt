<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\Indices;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class OpenCloseTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_indices_openclose_test';


	protected function setUp(): void
	{
		parent::setUp();
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		\usleep(100000);
	}


	public function testCloseIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);

		$result = $close->execute(self::INDEX);

		\Tester\Assert::true($result);
	}


	public function testOpenClosedIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);
		$close->execute(self::INDEX);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Open $open */
		$open = $this->container->getByType(\Spameri\Elastic\Model\Indices\Open::class);

		$result = $open->execute(self::INDEX);

		\Tester\Assert::true($result);
	}


	public function testOperationsOnClosedIndexFail(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);
		$close->execute(self::INDEX);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		// Inserting to closed index should fail
		\Tester\Assert::exception(
			static fn () => $insert->execute($entity, self::INDEX, false),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testSearchOnClosedIndexFails(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);
		$close->execute(self::INDEX);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\GetBy $getBy */
		$getBy = $this->container->getByType(\Spameri\Elastic\Model\GetBy::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		// Searching closed index should fail
		\Tester\Assert::exception(
			static fn () => $getBy->execute($elasticQuery, self::INDEX),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testCloseAndReopenAllowsOperations(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Close $close */
		$close = $this->container->getByType(\Spameri\Elastic\Model\Indices\Close::class);
		$close->execute(self::INDEX);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Open $open */
		$open = $this->container->getByType(\Spameri\Elastic\Model\Indices\Open::class);
		$open->execute(self::INDEX);

		\usleep(100000);

		// Now operations should work again
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::type('string', $id);
		\Tester\Assert::true(\strlen($id) > 0);
	}


	protected function tearDown(): void
	{
		// Try to open before delete in case test left it closed
		try {
			/** @var \Spameri\Elastic\Model\Indices\Open $open */
			$open = $this->container->getByType(\Spameri\Elastic\Model\Indices\Open::class);
			$open->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}

		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new OpenCloseTest())->run();
