<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class RemoveTest extends \SpameriTests\Elastic\AbstractTestCase
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


	public function testRemoveDeletesEntity(): void
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

		// Verify it exists
		$found = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);
		\Tester\Assert::notNull($found);

		// Remove it
		$success = $entityManager->remove($entity);
		\Tester\Assert::true($success);

		// Wait for ES to process delete
		\usleep(500000);

		// Verify it's gone
		\Tester\Assert::exception(
			static fn () => $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class),
			\Spameri\Elastic\Exception\DocumentNotFound::class,
		);
	}


	public function testRemoveFiresPreDeleteEvent(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::PRE_DELETE,
		);

		/** @var \Spameri\Elastic\EventManager $eventManager */
		$eventManager = $this->container->getByType(\Spameri\Elastic\EventManager::class);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::PRE_DELETE,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener,
		);

		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$entityManager->remove($entity);

		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($entity, $listener->calls[0]['entity']);
	}


	public function testRemoveFiresPostDeleteEvent(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_DELETE,
		);

		/** @var \Spameri\Elastic\EventManager $eventManager */
		$eventManager = $this->container->getByType(\Spameri\Elastic\EventManager::class);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_DELETE,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener,
		);

		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$entityManager->persist($entity);

		// Wait for ES to index
		\usleep(500000);

		$entityManager->remove($entity);

		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testRemoveReturnsBoolean(): void
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

		$result = $entityManager->remove($entity);

		\Tester\Assert::type('bool', $result);
		\Tester\Assert::true($result);
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

(new RemoveTest())->run();
