<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class ClassWithCollection
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Collection]
		public readonly array $items,
		public readonly string $regularProperty,
	)
	{
	}

}

/**
 * @testCase
 */
class CollectionAttributeTest extends \Tester\TestCase
{

	public function testCollectionTypeDetected(): void
	{
		$reflection = new \ReflectionClass(ClassWithCollection::class);
		$property = $reflection->getProperty('items');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Collection::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testRegularPropertyNotMarked(): void
	{
		$reflection = new \ReflectionClass(ClassWithCollection::class);
		$property = $reflection->getProperty('regularProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Collection::class);

		\Tester\Assert::same(0, \count($attributes));
	}


	public function testAttributeCanBeInstantiated(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\Collection();

		\Tester\Assert::type(\Spameri\Elastic\Mapping\Collection::class, $attribute);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\Collection::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(ClassWithCollection::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\Collection::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testAttributeHasNoParameters(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\Collection::class);
		$constructor = $reflection->getConstructor();

		\Tester\Assert::same(0, $constructor->getNumberOfParameters());
	}

}

(new CollectionAttributeTest())->run();
