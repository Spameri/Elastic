<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Mapping;

require_once __DIR__ . '/../../../bootstrap-unit.php';

class ClassWithElasticCollection
{

	public function __construct(
		#[\Spameri\Elastic\Mapping\ElasticCollection(class: \DateTime::class)]
		public readonly array $entities,
		#[\Spameri\Elastic\Mapping\ElasticCollection(class: \stdClass::class)]
		public readonly array $otherEntities,
	)
	{
	}

}

/**
 * @testCase
 */
class ElasticCollectionAttributeTest extends \Tester\TestCase
{

	public function testElasticEntityCollectionDetected(): void
	{
		$reflection = new \ReflectionClass(ClassWithElasticCollection::class);
		$property = $reflection->getProperty('entities');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testClassPropertyIsReadable(): void
	{
		$reflection = new \ReflectionClass(ClassWithElasticCollection::class);
		$property = $reflection->getProperty('entities');
		$attributes = $property->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class);
		$instance = $attributes[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance->class);
	}


	public function testDifferentClassValues(): void
	{
		$reflection = new \ReflectionClass(ClassWithElasticCollection::class);

		$property1 = $reflection->getProperty('entities');
		$instance1 = $property1->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class)[0]->newInstance();

		$property2 = $reflection->getProperty('otherEntities');
		$instance2 = $property2->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class)[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance1->class);
		\Tester\Assert::same(\stdClass::class, $instance2->class);
	}


	public function testAttributeHasClassProperty(): void
	{
		$attribute = new \Spameri\Elastic\Mapping\ElasticCollection(class: \ArrayObject::class);

		\Tester\Assert::same(\ArrayObject::class, $attribute->class);
	}


	public function testAttributeTargetPropertyAndParameter(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Mapping\ElasticCollection::class);
		$attributes = $reflection->getAttributes(\Attribute::class);

		\Tester\Assert::same(1, \count($attributes));

		$attributeInstance = $attributes[0]->newInstance();
		$expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;

		\Tester\Assert::same($expectedFlags, $attributeInstance->flags);
	}


	public function testCanBeAppliedToConstructorParameter(): void
	{
		$reflection = new \ReflectionClass(ClassWithElasticCollection::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class);

		\Tester\Assert::same(1, \count($attributes));
	}


	public function testParameterAttributeHasSameValue(): void
	{
		$reflection = new \ReflectionClass(ClassWithElasticCollection::class);
		$constructor = $reflection->getConstructor();
		$parameters = $constructor->getParameters();
		$attributes = $parameters[0]->getAttributes(\Spameri\Elastic\Mapping\ElasticCollection::class);
		$instance = $attributes[0]->newInstance();

		\Tester\Assert::same(\DateTime::class, $instance->class);
	}

}

(new ElasticCollectionAttributeTest())->run();
