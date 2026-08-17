<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class PersistTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing indexes or aliases with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_TITLE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_IMAGE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_TITLE, []);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testPersistNewEntityWithEmptyElasticId(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		\Tester\Assert::notSame('', $id);
		\Tester\Assert::false($entity->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId);
		\Tester\Assert::same($id, $entity->id()->value());
	}


	public function testPersistExistingEntityUpdates(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id1 = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(100000);

		// Persist again (update)
		$id2 = $entityManager->persist($entity);

		\Tester\Assert::same($id1, $id2);
	}


	public function testPersistEntityWithNestedEntities(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$imageId = $entityManager->persist($image);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$image,
		);

		$titleId = $entityManager->persist($title);

		\Tester\Assert::notSame('', $imageId);
		\Tester\Assert::notSame('', $titleId);
		\Tester\Assert::same($title->backdrop, $image);
	}


	public function testPersistFiresPrePersistEvent(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::PRE_PERSIST,
		);

		$this->container->addService('testPrePersistListener', $listener);

		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		/** @var \Spameri\Elastic\EventManager $eventManager */
		$eventManager = $this->container->getByType(\Spameri\Elastic\EventManager::class);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::PRE_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener,
		);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$entityManager->persist($entity);

		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($entity, $listener->calls[0]['entity']);
	}


	public function testPersistFiresPostPersistEvent(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		/** @var \Spameri\Elastic\EventManager $eventManager */
		$eventManager = $this->container->getByType(\Spameri\Elastic\EventManager::class);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
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

		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testPersistFiresPostCreateEventForNewEntity(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_CREATE,
		);

		/** @var \Spameri\Elastic\EventManager $eventManager */
		$eventManager = $this->container->getByType(\Spameri\Elastic\EventManager::class);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_CREATE,
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

		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testPersistReturnsIdString(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		\Tester\Assert::type('string', $id);
		\Tester\Assert::true(\strlen($id) > 0);
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

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
	}

}

(new PersistTest())->run();
