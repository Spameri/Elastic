<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class EntityWithAttribute
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Entity(class: \DateTime::class)]
		public readonly mixed $property,
		#[\Spameri\Elastic\Mapping\Entity(class: \stdClass::class)]
		public readonly mixed $anotherProperty,
	)
	{
	}

}

/**
 * @testCase
 */
class EntityAttributeTest extends \Tester\TestCase
{

	public function testAttributeAppliedToProperty(): void
	{
		$reflection = new \ReflectionClass(EntityWithAttribute::class);
		$property = $reflection->getProperty('property');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Entity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testReflectionReadsPropertyAttributeCorrectly(): void
	{
		$reflection = new \ReflectionClass(EntityWithAttribute::class);
		$property = $reflection->getProperty('property');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Entity::class);
		$instance = $attributes[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance->class);
	}


	public function testDifferentClassValues(): void
	{
		$reflection = new \ReflectionClass(EntityWithAttribute::class);

		$property1 = $reflection->getProperty('property');
		$attributes1 = $property1->getAttributes(\Spameri\Elastic\Mapping\Entity::class);
		$instance1 = $attributes1[0]->newInstance();

		$property2 = $reflection->getProperty('anotherProperty');
		$attributes2 = $property2->getAttributes(\Spameri\Elastic\Mapping\Entity::class);
		$instance2 = $attributes2[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance1->class);
		\Tester\Assert::same(\stdClass::class, $instance2->class);
	}


	public function testAttributeHasClassProperty(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\Entity(class: \ArrayObject::class);

		\Tester\Assert::same(\ArrayObject::class, $attribute->class);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\Entity::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(EntityWithAttribute::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\Entity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testParameterAttributeHasSameValue(): void
	{
		$reflection = new \ReflectionClass(EntityWithAttribute::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\Entity::class);
		$instance = $attributes[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance->class);
	}

}

(new EntityAttributeTest())->run();
