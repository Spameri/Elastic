<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class ClassWithSTIEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\STIEntity]
		public readonly mixed $stiProperty,
		public readonly string $normalProperty,
	)
	{
	}

}

/**
 * @testCase
 */
class STIEntityAttributeTest extends \Tester\TestCase
{

	public function testSTIParentIdentified(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIEntity::class);
		$property = $reflection->getProperty('stiProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\STIEntity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testNormalPropertyNotMarked(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIEntity::class);
		$property = $reflection->getProperty('normalProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\STIEntity::class);

		\Tester\Assert::same(0, \count($attributes));
	}


	public function testAttributeCanBeInstantiated(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\STIEntity();

		\Tester\Assert::type(\Spameri\Elastic\Mapping\STIEntity::class, $attribute);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\STIEntity::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIEntity::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\STIEntity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testAttributeHasNoConstructor(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\STIEntity::class);
		$constructor = $reflection->getConstructor();

		// STIEntity has no explicit constructor
		\Tester\Assert::null($constructor);
	}

}

(new STIEntityAttributeTest())->run();
