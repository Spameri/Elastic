<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ArrayValueTest extends \Tester\TestCase
{

	public function testValidateArrayInput(): void
	{
		$array = ['a', 'b', 'c'];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($array, 'items');

		\Tester\Assert::same(['a', 'b', 'c'], $value->getValue());
	}


	public function testKeyReturnsFieldName(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue(['test'], 'tags');

		\Tester\Assert::same('tags', $value->key());
	}


	public function testEmptyArray(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue([], 'emptyField');

		\Tester\Assert::same([], $value->getValue());
	}


	public function testNestedArray(): void
	{
		$nested = [
			'level1' => [
				'level2' => [
					'value' => 'deep',
				],
			],
		];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($nested, 'nestedData');

		\Tester\Assert::same($nested, $value->getValue());
	}


	public function testMixedTypes(): void
	{
		$mixed = [
			'string' => 'value',
			'int' => 42,
			'bool' => true,
			'null' => null,
			'float' => 3.14,
		];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($mixed, 'mixed');

		\Tester\Assert::same($mixed, $value->getValue());
	}


	public function testNumericIndexedArray(): void
	{
		$indexed = [1, 2, 3, 4, 5];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($indexed, 'numbers');

		\Tester\Assert::same([1, 2, 3, 4, 5], $value->getValue());
	}


	public function testAssociativeArray(): void
	{
		$assoc = [
			'name' => 'John',
			'age' => 30,
			'city' => 'Prague',
		];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($assoc, 'person');

		\Tester\Assert::same($assoc, $value->getValue());
	}


	public function testArrayWithObjects(): void
	{
		$obj = new \stdClass();
		$obj->property = 'value';
		$array = ['object' => $obj];
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($array, 'withObjects');

		\Tester\Assert::same($array, $value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue([], 'field');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Import\ArrayValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testGetValueReturnsArray(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue(['test'], 'field');

		\Tester\Assert::type('array', $value->getValue());
	}


	public function testKeyReturnsString(): void
	{
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue([], 'field');

		\Tester\Assert::type('string', $value->key());
	}


	public function testLargeArray(): void
	{
		$large = \range(1, 1000);
		$value = new \Spameri\Elastic\Entity\Import\ArrayValue($large, 'largeArray');

		\Tester\Assert::same($large, $value->getValue());
		\Tester\Assert::same(1000, \count($value->getValue()));
	}

}

(new ArrayValueTest())->run();
