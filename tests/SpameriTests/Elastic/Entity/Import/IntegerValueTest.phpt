<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class IntegerValueTest extends \Tester\TestCase
{

	public function testValidateIntegerInput(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(42, 'count');

		\Tester\Assert::same(42, $value->getValue());
	}


	public function testKeyReturnsFieldName(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(100, 'quantity');

		\Tester\Assert::same('quantity', $value->key());
	}


	public function testZeroValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(0, 'zeroField');

		\Tester\Assert::same(0, $value->getValue());
	}


	public function testNegativeValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(-500, 'negativeField');

		\Tester\Assert::same(-500, $value->getValue());
	}


	public function testLargePositiveValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(\PHP_INT_MAX, 'largeField');

		\Tester\Assert::same(\PHP_INT_MAX, $value->getValue());
	}


	public function testLargeNegativeValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(\PHP_INT_MIN, 'smallField');

		\Tester\Assert::same(\PHP_INT_MIN, $value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(1, 'field');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Import\IntegerValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testGetValueReturnsInt(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(123, 'field');

		\Tester\Assert::type('int', $value->getValue());
	}


	public function testKeyReturnsString(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\IntegerValue(123, 'field');

		\Tester\Assert::type('string', $value->key());
	}

}

(new IntegerValueTest())->run();
