<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class BoolValueTest extends \Tester\TestCase
{

	public function testValidateTrueBoolean(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'isActive');

		\Tester\Assert::true($value->getValue());
	}


	public function testValidateFalseBoolean(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\BoolValue(false, 'isDeleted');

		\Tester\Assert::false($value->getValue());
	}


	public function testKeyReturnsFieldName(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'enabled');

		\Tester\Assert::same('enabled', $value->key());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'field');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Import\BoolValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testGetValueReturnsBool(): void
	{
		$trueValue = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'field');
		$falseValue = new \Spameri\Elastic\Entity\Import\BoolValue(false, 'field');

		\Tester\Assert::type('bool', $trueValue->getValue());
		\Tester\Assert::type('bool', $falseValue->getValue());
	}


	public function testKeyReturnsString(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'field');

		\Tester\Assert::type('string', $value->key());
	}


	public function testDifferentFieldNames(): void
	{
		$value1 = new \Spameri\Elastic\Entity\Import\BoolValue(true, 'isPublished');
		$value2 = new \Spameri\Elastic\Entity\Import\BoolValue(false, 'isDraft');

		\Tester\Assert::same('isPublished', $value1->key());
		\Tester\Assert::same('isDraft', $value2->key());
	}

}

(new BoolValueTest())->run();
