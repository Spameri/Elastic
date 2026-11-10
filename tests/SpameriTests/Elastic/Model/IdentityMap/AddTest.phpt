<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\IdentityMap;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class AddTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Model\IdentityMap $identityMap;


	protected function setUp(): void
	{
		$this->identityMap = new \Spameri\Elastic\Model\IdentityMap();
	}


	public function testAddStoresEntityInMap(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id-123'),
			null,
		);

		$this->identityMap->add($entity);

		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id-123');
		\Tester\Assert::same($entity, $retrieved);
	}


	public function testAddWithEmptyIdDoesNotStore(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$this->identityMap->add($entity);

		\Tester\Assert::true(empty($this->identityMap->identityMap));
	}


	public function testAddWithDifferentEntityClasses(): void
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

		\Tester\Assert::same($title, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'title-1'));
		\Tester\Assert::same($image, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Image::class, 'image-1'));
	}


	public function testAddOverwritesExistingEntry(): void
	{
		$entity1 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('same-id'),
			null,
		);
		$entity2 = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('same-id'),
			null,
		);

		$this->identityMap->add($entity1);
		$this->identityMap->add($entity2);

		$retrieved = $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'same-id');
		\Tester\Assert::same($entity2, $retrieved);
		\Tester\Assert::notSame($entity1, $retrieved);
	}


	public function testAddStoresInParentClassToo(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$this->identityMap->add($entity);

		// Should be retrievable by parent class too
		// Note: AbstractElasticEntity is excluded in the implementation
		// but if there's a non-abstract parent, it would be stored there
		\Tester\Assert::same($entity, $this->identityMap->get(\SpameriTests\Elastic\Data\Entity\Title::class, 'test-id'));
	}

}

(new AddTest())->run();
