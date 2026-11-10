<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class FloatValueTest extends \Tester\TestCase
{

	public function testCreateFromFloat(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(12.34, 'price');

		\Tester\Assert::same(12.34, $value->getValue());
		\Tester\Assert::same('price', $value->key());
	}


	public function testCreateFromInteger(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(42.0, 'amount');

		\Tester\Assert::same(42.0, $value->getValue());
		\Tester\Assert::same('amount', $value->key());
	}


	public function testCreateWithZero(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(0.0, 'zero');

		\Tester\Assert::same(0.0, $value->getValue());
	}


	public function testCreateWithNegativeValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(-99.99, 'discount');

		\Tester\Assert::same(-99.99, $value->getValue());
	}


	public function testCreateWithVerySmallValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(0.0001, 'tiny');

		\Tester\Assert::same(0.0001, $value->getValue());
	}


	public function testCreateWithVeryLargeValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(999999999.99, 'huge');

		\Tester\Assert::same(999999999.99, $value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(1.5, 'test');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testKeyReturnsCorrectKey(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(10.0, 'custom_key');

		\Tester\Assert::same('custom_key', $value->key());
	}


	public function testGetValueReturnsFloat(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\FloatValue(5.5, 'test');

		\Tester\Assert::type('float', $value->getValue());
	}

}

(new FloatValueTest())->run();
