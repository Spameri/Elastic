<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class NoValueTest extends \Tester\TestCase
{

	public function testKeyReturnsNull(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\NoValue();

		\Tester\Assert::null($value->key());
	}


	public function testGetValueReturnsNull(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\NoValue();

		\Tester\Assert::null($value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\NoValue();

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testMultipleInstancesReturnSameValues(): void
	{
		$value1 = new \Spameri\Elastic\Entity\Import\NoValue();
		$value2 = new \Spameri\Elastic\Entity\Import\NoValue();

		\Tester\Assert::same($value1->key(), $value2->key());
		\Tester\Assert::same($value1->getValue(), $value2->getValue());
	}


	public function testNoValueRepresentsAbsenceOfValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\NoValue();

		// NoValue is used when there's no value at all (key doesn't exist)
		\Tester\Assert::null($value->key());
		\Tester\Assert::null($value->getValue());
	}

}

(new NoValueTest())->run();
