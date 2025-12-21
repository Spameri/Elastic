<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class StringValueTest extends \Tester\TestCase
{

	public function testValidateStringInput(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('test value', 'fieldName');

		\Tester\Assert::same('test value', $value->getValue());
	}


	public function testKeyReturnsFieldName(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('test', 'myField');

		\Tester\Assert::same('myField', $value->key());
	}


	public function testEmptyStringValue(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('', 'emptyField');

		\Tester\Assert::same('', $value->getValue());
	}


	public function testUnicodeString(): void
	{
		$unicodeString = 'Hello 世界 🌍 Привет';
		$value = new \Spameri\Elastic\Entity\Import\StringValue($unicodeString, 'unicode');

		\Tester\Assert::same($unicodeString, $value->getValue());
	}


	public function testLongString(): void
	{
		$longString = \str_repeat('a', 10000);
		$value = new \Spameri\Elastic\Entity\Import\StringValue($longString, 'longField');

		\Tester\Assert::same($longString, $value->getValue());
	}


	public function testSpecialCharacters(): void
	{
		$special = "Tab:\t Newline:\n Quote:\"";
		$value = new \Spameri\Elastic\Entity\Import\StringValue($special, 'special');

		\Tester\Assert::same($special, $value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('test', 'field');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Import\StringValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testGetValueReturnsString(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('test', 'field');

		\Tester\Assert::type('string', $value->getValue());
	}


	public function testKeyReturnsString(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\StringValue('test', 'field');

		\Tester\Assert::type('string', $value->key());
	}

}

(new StringValueTest())->run();
