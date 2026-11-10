<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Value;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class StringValueTest extends \Tester\TestCase
{

	public function testCreateFromString(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\StringValue('hello');

		\Tester\Assert::same('hello', $value->value());
	}


	public function testEmptyStringHandling(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\StringValue('');

		\Tester\Assert::same('', $value->value());
	}


	public function testCreateFromLongString(): void
	{
		$longString = \str_repeat('a', 10000);
		$value = new \Spameri\Elastic\Entity\Value\StringValue($longString);

		\Tester\Assert::same($longString, $value->value());
	}


	public function testUnicodeStringHandling(): void
	{
		$unicodeString = 'Hello 世界 🌍 Привет';
		$value = new \Spameri\Elastic\Entity\Value\StringValue($unicodeString);

		\Tester\Assert::same($unicodeString, $value->value());
	}


	public function testSpecialCharacters(): void
	{
		$specialChars = "Tab:\t Newline:\n Quote:\" Backslash:\\";
		$value = new \Spameri\Elastic\Entity\Value\StringValue($specialChars);

		\Tester\Assert::same($specialChars, $value->value());
	}


	public function testValueReturnsCorrectType(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\StringValue('test');

		\Tester\Assert::type('string', $value->value());
	}


	public function testImplementsValueInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\StringValue('test');

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Value\StringValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}

}

(new StringValueTest())->run();
