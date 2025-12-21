<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class EmptyValueTest extends \Tester\TestCase
{

	public function testKeyReturnsProvidedKey(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('field_name');

		\Tester\Assert::same('field_name', $value->key());
	}


	public function testGetValueReturnsNull(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('test');

		\Tester\Assert::null($value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('key');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testEmptyValueHasKeyButNullValue(): void
	{
		// EmptyValue represents a field that exists but has no value (empty/null)
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('description');

		\Tester\Assert::same('description', $value->key());
		\Tester\Assert::null($value->getValue());
	}


	public function testDifferentKeysCreateDifferentInstances(): void
	{
		$value1 = new \Spameri\Elastic\Entity\Import\EmptyValue('key1');
		$value2 = new \Spameri\Elastic\Entity\Import\EmptyValue('key2');

		\Tester\Assert::notSame($value1->key(), $value2->key());
		\Tester\Assert::same($value1->getValue(), $value2->getValue()); // Both null
	}


	public function testEmptyStringKey(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('');

		\Tester\Assert::same('', $value->key());
		\Tester\Assert::null($value->getValue());
	}


	public function testKeyWithSpecialCharacters(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\EmptyValue('field.nested.value');

		\Tester\Assert::same('field.nested.value', $value->key());
	}

}

(new EmptyValueTest())->run();
