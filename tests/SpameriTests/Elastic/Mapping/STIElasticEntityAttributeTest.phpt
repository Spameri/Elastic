<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class ClassWithSTIElasticEntity
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\STIElasticEntity]
		public readonly mixed $stiElasticProperty,
		public readonly string $normalProperty,
	)
	{
	}

}

/**
 * @testCase
 */
class STIElasticEntityAttributeTest extends \Tester\TestCase
{

	public function testSTIElasticEntityHandling(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIElasticEntity::class);
		$property = $reflection->getProperty('stiElasticProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\STIElasticEntity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testNormalPropertyNotMarked(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIElasticEntity::class);
		$property = $reflection->getProperty('normalProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\STIElasticEntity::class);

		\Tester\Assert::same(0, \count($attributes));
	}


	public function testAttributeCanBeInstantiated(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\STIElasticEntity();

		\Tester\Assert::type(\Spameri\Elastic\Mapping\STIElasticEntity::class, $attribute);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\STIElasticEntity::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(ClassWithSTIElasticEntity::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\STIElasticEntity::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testAttributeHasNoConstructor(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\STIElasticEntity::class);
		$constructor = $reflection->getConstructor();

		// STIElasticEntity has no explicit constructor
		\Tester\Assert::null($constructor);
	}


	public function testDifferentFromSTIEntity(): void
	{
		$stiEntity = new \Spameri\Elastic\Mapping\STIEntity();
		$stiElasticEntity = new \Spameri\Elastic\Mapping\STIElasticEntity();

		// They are different classes
		\Tester\Assert::false($stiElasticEntity instanceof \Spameri\Elastic\Mapping\STIEntity);
		\Tester\Assert::false($stiEntity instanceof \Spameri\Elastic\Mapping\STIElasticEntity);
	}

}

(new STIElasticEntityAttributeTest())->run();
