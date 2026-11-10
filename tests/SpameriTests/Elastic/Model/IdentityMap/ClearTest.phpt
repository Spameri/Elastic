<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\IdentityMap;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * Tests for clearing IdentityMap data.
 * Note: IdentityMap uses public arrays that can be cleared directly.
 *
 * @testCase
 */
class ClearTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testClearIdentityMapRemovesAllEntities(): void
	{
		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-1'),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('id-2'),
			null,
		);

		$this->identityMap->add($entity1);
		$this->identityMap->add($entity2);

		// Verify entities are stored
		\Tester\Assert::same($entity1, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-1'));
		\Tester\Assert::same($entity2, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-2'));

		// Clear by resetting the array
		$this->identityMap->identityMap = [];

		// Verify entities are gone
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-1'));
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'id-2'));
	}


	public function testClearPersistedRemovesChangeTracking(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->markInserted($entity);

		// Entity should not be changed
		\Tester\Assert::false($this->identityMap->isChanged($entity));

		// Clear persisted tracking
		$this->identityMap->persisted = [];

		// Now entity should be considered changed again
		\Tester\Assert::true($this->identityMap->isChanged($entity));
	}


	public function testClearSpecificClassOnly(): void
	{
		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-1'),
			null,
		);
		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-1'),
			null,
		);

		$this->identityMap->add($title);
		$this->identityMap->add($image);

		// Clear only Title class
		unset($this->identityMap->identityMap[\SpameriTests\Elastic\Data\Entity\Title::class]);

		// Title should be gone
		\Tester\Assert::null($this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'title-1'));
		// Image should still exist
		\Tester\Assert::same($image, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, 'image-1'));
	}


	public function testClearCreatingEntityList(): void
	{
		// creatingEntityList is used during entity creation to prevent infinite loops
		$this->identityMap->creatingEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id'] = true;

		\Tester\Assert::true(isset($this->identityMap->creatingEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id']));

		$this->identityMap->creatingEntityList = [];

		\Tester\Assert::false(isset($this->identityMap->creatingEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id']));
	}


	public function testClearUninitializedEntityList(): void
	{
		// uninitializedEntityList tracks entities that need to be resolved
		$this->identityMap->uninitializedEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]['test-id']['property']['entity-id'] = \SpameriTests\Elastic\Data\Entity\Image::class;

		\Tester\Assert::true(isset($this->identityMap->uninitializedEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]));

		$this->identityMap->uninitializedEntityList = [];

		\Tester\Assert::false(isset($this->identityMap->uninitializedEntityList[\SpameriTests\Elastic\Data\Entity\Title::class]));
	}


	public function testNewIdentityMapIsEmpty(): void
	{
		$freshMap = new \Spameri\Elastic\Model\IdentityMap();

		\Tester\Assert::true(empty($freshMap->identityMap));
		\Tester\Assert::true(empty($freshMap->persisted));
		\Tester\Assert::true(empty($freshMap->creatingEntityList));
		\Tester\Assert::true(empty($freshMap->uninitializedEntityList));
	}

}

(new ClearTest())->run();
