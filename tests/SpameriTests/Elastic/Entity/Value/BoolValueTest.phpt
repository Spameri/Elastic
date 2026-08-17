<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Value;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class BoolValueTest extends \Tester\TestCase
{

	public function testCreateFromTrue(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\BoolValue(true);

		\Tester\Assert::true($value->value());
	}


	public function testCreateFromFalse(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\BoolValue(false);

		\Tester\Assert::false($value->value());
	}


	public function testValueReturnsCorrectType(): void
	{
		$trueValue = new \Spameri\Elastic\Entity\Value\BoolValue(true);
		$falseValue = new \Spameri\Elastic\Entity\Value\BoolValue(false);

		\Tester\Assert::type('bool', $trueValue->value());
		\Tester\Assert::type('bool', $falseValue->value());
	}


	public function testImplementsValueInterface(): void
	{
		$value = new \Spameri\Elastic\Entity\Value\BoolValue(true);

		\Tester\Assert::type(\Spameri\Elastic\Entity\ValueInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Value\BoolValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}

}

(new BoolValueTest())->run();
