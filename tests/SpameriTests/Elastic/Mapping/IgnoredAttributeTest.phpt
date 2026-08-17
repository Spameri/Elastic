<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class ClassWithIgnored
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\Ignored]
		public readonly mixed $ignoredProperty,
		public readonly string $normalProperty,
		#[\Spameri\Elastic\Mapping\Ignored]
		public readonly mixed $anotherIgnored,
	)
	{
	}

}

/**
 * @testCase
 */
class IgnoredAttributeTest extends \Tester\TestCase
{

	public function testPropertyExcludedFromSerialization(): void
	{
		$reflection = new \ReflectionClass(ClassWithIgnored::class);
		$property = $reflection->getProperty('ignoredProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Ignored::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testNormalPropertyNotMarked(): void
	{
		$reflection = new \ReflectionClass(ClassWithIgnored::class);
		$property = $reflection->getProperty('normalProperty');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Ignored::class);

		\Tester\Assert::same(0, \count($attributes));
	}


	public function testMultipleIgnoredProperties(): void
	{
		$reflection = new \ReflectionClass(ClassWithIgnored::class);

		$ignoredCount = 0;
		foreach ($reflection->getProperties() as $property) {
			$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\Ignored::class);
			if (\count($attributes) > 0) {
				$ignoredCount++;
			}
		}

		\Tester\Assert::same(2, $ignoredCount);
	}


	public function testAttributeCanBeInstantiated(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\Ignored();

		\Tester\Assert::type(\Spameri\Elastic\Mapping\Ignored::class, $attribute);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\Ignored::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(ClassWithIgnored::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\Ignored::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testAttributeHasNoParameters(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\Ignored::class);
		$constructor = $reflection->getConstructor();

		\Tester\Assert::same(0, $constructor->getNumberOfParameters());
	}

}

(new IgnoredAttributeTest())->run();
