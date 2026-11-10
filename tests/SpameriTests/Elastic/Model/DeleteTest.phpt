<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class DeleteTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_delete_test';


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


	public function testDeleteById(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		// Verify it exists
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);
		$result = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);
		\Tester\Assert::same($id, $result->hit()->id());

		// Delete it
		/** @var \Spameri\Elastic\Model\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Delete::class);
		$success = $delete->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);

		\Tester\Assert::true($success);

		// Verify it's gone
		\Tester\Assert::exception(
			static fn () => $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testDeleteNonExistentThrowsException(): void
	{
		/** @var \Spameri\Elastic\Model\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Delete::class);

		\Tester\Assert::exception(
			static fn () => $delete->execute(
				new \Spameri\Elastic\Entity\Property\ElasticId('nonexistent-id-12345'),
				self::INDEX,
			),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testDeleteFromSpecificIndex(): void
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

		/** @var \Spameri\Elastic\Model\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Delete::class);

		// Delete from index A only
		$success = $delete->execute(new \Spameri\Elastic\Entity\Property\ElasticId($idA), $indexA);
		\Tester\Assert::true($success);

		// Entity in B should still exist
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);
		$resultB = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($idB), $indexB);
		\Tester\Assert::same($idB, $resultB->hit()->id());

		// Entity A should be gone
		\Tester\Assert::exception(
			static fn () => $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($idA), $indexA),
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);

		// Cleanup
		/** @var \Spameri\Elastic\Model\Indices\Delete $indexDelete */
		$indexDelete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$indexDelete->execute($indexA);
		$indexDelete->execute($indexB);
	}


	public function testDeleteReturnsBoolean(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Delete::class);

		$result = $delete->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);

		\Tester\Assert::type('bool', $result);
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

(new DeleteTest())->run();
