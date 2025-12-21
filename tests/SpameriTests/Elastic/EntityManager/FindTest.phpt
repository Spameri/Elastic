<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class FindTest extends \SpameriTests\Elastic\AbstractTestCase
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


	public function testFindByIdReturnsEntity(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$found = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::notNull($found);
		\Tester\Assert::same($id, $found->id()->value());
	}


	public function testFindWithNonExistentIdThrowsException(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		\Tester\Assert::exception(
			static fn () => $entityManager->find('nonexistent-id-12345', \SpameriTests\Elastic\Data\Entity\Title::class),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testFindReturnsSameInstanceFromIdentityMap(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$found1 = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);
		$found2 = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Should return same instance (Identity Map pattern)
		\Tester\Assert::same($found1, $found2);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_TITLE);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
	}

}

(new FindTest())->run();
