<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Value;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class IntegerValueTest extends \Tester\TestCase
{

	public function testCreateFromPositiveInt(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(42);

		\Tester\Assert::same(42, $value->value());
	}


	public function testCreateFromNegativeInt(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(-100);

		\Tester\Assert::same(-100, $value->value());
	}


	public function testCreateFromZero(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(0);

		\Tester\Assert::same(0, $value->value());
	}


	public function testCreateFromLargeInt(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(\PHP_INT_MAX);

		\Tester\Assert::same(\PHP_INT_MAX, $value->value());
	}


	public function testCreateFromSmallInt(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(\PHP_INT_MIN);

		\Tester\Assert::same(\PHP_INT_MIN, $value->value());
	}


	public function testValueReturnsCorrectType(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(123);

		\Tester\Assert::type('int', $value->value());
	}


	public function testImplementsValueInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\IntegerValue(1);

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Value\IntegerValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}

}

(new IntegerValueTest())->run();
