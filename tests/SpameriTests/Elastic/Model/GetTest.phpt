<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class GetTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_get_test';


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


	public function testGetById(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		// Wait for ES to index
		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		$result = $get->execute(
			new \Spameri\Elastic\Entity\Property\ElasticId($id),
			self::INDEX,
		);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSingle::class, $result);
		\Tester\Assert::same($id, $result->hit()->id());
	}


	/**
	 * The exception says which situation it is now.
	 *
	 * It used to be ElasticSearch, not by design but because every client exception was wrapped the
	 * same way - so a missing document and an unreachable cluster arrived as the same type, while
	 * findOneBy() raised DocumentNotFound for that very situation.
	 */
	public function testGetNonExistentThrowsDocumentNotFound(): void
	{
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		\Tester\Assert::exception(
			static fn () => $get->execute(
				new \Spameri\Elastic\Entity\Property\ElasticId('nonexistent-id-12345'),
				self::INDEX,
			),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testGetFromSpecificIndex(): void
	{
		$indexA = self::INDEX . '_a';
		$indexB = self::INDEX . '_b';

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute($indexA, []);
		$create->execute($indexB, []);

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entityA = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$entityB = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$idA = $insert->execute($entityA, $indexA, false);
		$idB = $insert->execute($entityB, $indexB, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		// Get from index A
		$resultA = $get->execute(
			new \Spameri\Elastic\Entity\Property\ElasticId($idA),
			$indexA,
		);
		\Tester\Assert::same($idA, $resultA->hit()->id());

		// Get from index B
		$resultB = $get->execute(
			new \Spameri\Elastic\Entity\Property\ElasticId($idB),
			$indexB,
		);
		\Tester\Assert::same($idB, $resultB->hit()->id());

		// Trying to get A's ID from B should fail - as a document that is not there, which is what
		// it is, rather than as an unspecified Elasticsearch failure.
		\Tester\Assert::exception(
			static fn () => $get->execute(
				new \Spameri\Elastic\Entity\Property\ElasticId($idA),
				$indexB,
			),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);

		// Cleanup extra indexes
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute($indexA);
		$delete->execute($indexB);
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

(new GetTest())->run();
