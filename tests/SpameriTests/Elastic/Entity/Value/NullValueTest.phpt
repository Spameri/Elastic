<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Value;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class NullValueTest extends \Tester\TestCase
{

	public function testRepresentsNullCorrectly(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\NullValue();

		\Tester\Assert::null($value->value());
	}


	public function testValueReturnsNull(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\NullValue();

		\Tester\Assert::same(null, $value->value());
	}


	public function testMultipleInstancesReturnNull(): void
	{
		$value1 = new \Spameri\Elastic\Entity\Value\NullValue();
		$value2 = new \Spameri\Elastic\Entity\Value\NullValue();

		\Tester\Assert::same($value1->value(), $value2->value());
	}


	public function testImplementsValueInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\NullValue();

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Value\NullValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testNoConstructorArguments(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Value\NullValue::class);
		$constructor = $reflection->getConstructor();

		\Tester\Assert::same(0, $constructor->getNumberOfRequiredParameters());
	}

}

(new NullValueTest())->run();
