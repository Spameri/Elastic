<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class FindAllTest extends \SpameriTests\Elastic\AbstractTestCase
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


	public function testFindAllReturnsAllEntities(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create 3 entities
		$count = 3;
		for ($i = 0; $i < $count; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\Title(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				null,
			);
			$entityManager->persist($entity);
		}

		// Wait for ES to index
		\usleep(500000);

		$collection = $entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::same($count, $collection->count());
	}


	public function testFindAllOnEmptyIndex(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = $entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\ElasticEntityCollection::class, $collection);
		\Tester\Assert::same(0, $collection->count());
	}


	public function testFindAllReturnsCorrectEntityType(): void
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

		$collection = $entityManager->findAll(\SpameriTests\Elastic\Data\Entity\Title::class);

		foreach ($collection as $item) {
			\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\Title::class, $item);
		}
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

(new FindAllTest())->run();
