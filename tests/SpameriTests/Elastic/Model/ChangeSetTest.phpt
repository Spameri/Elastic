<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ChangeSetTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\ChangeSet $changeSet;


	protected function setUp(): void
	{
		$this->changeSet = new \Spameri\Elastic\Model\ChangeSet();
	}


	public function testMarkExistingMarksEntity(): void
	{
		$entity = new \stdClass();

		$this->changeSet->markExisting($entity);

		\Tester\Assert::true($this->changeSet->isExisting($entity));
	}


	public function testIsExistingReturnsFalseForNewEntity(): void
	{
		$entity = new \stdClass();

		\Tester\Assert::false($this->changeSet->isExisting($entity));
	}


	public function testDifferentObjectsAreTrackedSeparately(): void
	{
		$entity1 = new \stdClass();
		$entity2 = new \stdClass();

		$this->changeSet->markExisting($entity1);

		\Tester\Assert::true($this->changeSet->isExisting($entity1));
		\Tester\Assert::false($this->changeSet->isExisting($entity2));
	}


	public function testWorksWithDifferentObjectTypes(): void
	{
		$stdClass = new \stdClass();
		$arrayObject = new \ArrayObject([]);
		$dateTime = new \DateTime();

		$this->changeSet->markExisting($stdClass);
		$this->changeSet->markExisting($arrayObject);

		\Tester\Assert::true($this->changeSet->isExisting($stdClass));
		\Tester\Assert::true($this->changeSet->isExisting($arrayObject));
		\Tester\Assert::false($this->changeSet->isExisting($dateTime));
	}


	public function testSameObjectInstanceReturnsTrue(): void
	{
		$entity = new \stdClass();

		$this->changeSet->markExisting($entity);

		// Same instance should be tracked
		$reference = $entity;
		\Tester\Assert::true($this->changeSet->isExisting($reference));
	}


	public function testClonedObjectIsNotMarked(): void
	{
		$entity = new \stdClass();
		$entity->value = 'test';

		$this->changeSet->markExisting($entity);

		$clone = clone $entity;

		\Tester\Assert::true($this->changeSet->isExisting($entity));
		\Tester\Assert::false($this->changeSet->isExisting($clone));
	}


	public function testMultipleMarksOfSameObjectDoNotCauseIssues(): void
	{
		$entity = new \stdClass();

		$this->changeSet->markExisting($entity);
		$this->changeSet->markExisting($entity);
		$this->changeSet->markExisting($entity);

		\Tester\Assert::true($this->changeSet->isExisting($entity));
	}


	public function testWorksWithElasticEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		\Tester\Assert::false($this->changeSet->isExisting($entity));

		$this->changeSet->markExisting($entity);

		\Tester\Assert::true($this->changeSet->isExisting($entity));
	}


	public function testCreatedArrayStructure(): void
	{
		$entity1 = new \stdClass();
		$entity2 = new \ArrayObject([]);

		$this->changeSet->markExisting($entity1);
		$this->changeSet->markExisting($entity2);

		// Verify the internal structure is organized by class
		\Tester\Assert::true(isset($this->changeSet->created[\stdClass::class]));
		\Tester\Assert::true(isset($this->changeSet->created[\ArrayObject::class]));
	}

}

(new ChangeSetTest())->run();
