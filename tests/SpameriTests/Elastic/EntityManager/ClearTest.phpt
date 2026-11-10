<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EntityManager;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for EntityManager clear() method.
 *
 * @testCase
 */
class ClearTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing indexes
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


	public function testClearRemovesEntitiesFromIdentityMap(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(100000);

		// Load entity to populate identity map
		$loadedEntity = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Verify entity is in identity map
		\Tester\Assert::notNull($identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, $id));

		// Clear entity manager
		$entityManager->clear();

		// Entity should be removed from identity map
		\Tester\Assert::null($identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, $id));
	}


	public function testClearRemovesChangeSetTracking(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(100000);

		// Load entity - this should mark it as existing in ChangeSet
		$loadedEntity = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Verify entity is tracked as existing
		\Tester\Assert::true($changeSet->isExisting($loadedEntity));

		// Clear entity manager
		$entityManager->clear();

		// Entity should no longer be tracked as existing
		\Tester\Assert::false($changeSet->isExisting($loadedEntity));
	}


	public function testClearAllowsFreshEntityLoad(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(100000);

		// Load entity first time
		$firstLoad = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);

		// Load entity second time (should be same instance from identity map)
		$secondLoad = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);
		\Tester\Assert::same($firstLoad, $secondLoad);

		// Clear entity manager
		$entityManager->clear();

		// Load entity third time (should be new instance after clear)
		$thirdLoad = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);
		\Tester\Assert::notSame($firstLoad, $thirdLoad);
		\Tester\Assert::notSame($secondLoad, $thirdLoad);
	}


	public function testClearDoesNotAffectPersistedData(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(100000);

		// Clear entity manager
		$entityManager->clear();

		// Data should still be retrievable from ElasticSearch
		$loadedEntity = $entityManager->find($id, \SpameriTests\Elastic\Data\Entity\Title::class);
		\Tester\Assert::notNull($loadedEntity);
		\Tester\Assert::same($id, $loadedEntity->id()->value());
	}


	public function testClearOnEmptyEntityManagerDoesNotThrow(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Should not throw any exceptions
		$entityManager->clear();

		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		\Tester\Assert::true(empty($identityMap->identityMap));
		\Tester\Assert::true(empty($changeSet->created));
	}


	public function testClearWithMultipleEntities(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$titleId = $entityManager->persist($title);
		$imageId = $entityManager->persist($image);

		// Wait for ES to index
		\usleep(100000);

		// Load entities
		$loadedTitle = $entityManager->find($titleId, \SpameriTests\Elastic\Data\Entity\Title::class);
		$loadedImage = $entityManager->find($imageId, \SpameriTests\Elastic\Data\Entity\Image::class);

		// Verify entities are in identity map
		\Tester\Assert::notNull($identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, $titleId));
		\Tester\Assert::notNull($identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, $imageId));

		// Clear
		$entityManager->clear();

		// Both should be gone
		\Tester\Assert::null($identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, $titleId));
		\Tester\Assert::null($identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, $imageId));
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

(new ClearTest())->run();
