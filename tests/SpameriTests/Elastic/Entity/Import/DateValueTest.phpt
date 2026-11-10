<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Import;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DateValueTest extends \Tester\TestCase
{

	public function testValidateDateInput(): void
	{
		$dateTime = new \DateTime('2024-01-15 10:30:00');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'createdAt');

		\Tester\Assert::same('2024-01-15 10:30:00', $value->getValue());
	}


	public function testKeyReturnsFieldName(): void
	{
		$dateTime = new \DateTime('2024-06-20');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'publishDate');

		\Tester\Assert::same('publishDate', $value->key());
	}


	public function testCustomFormat(): void
	{
		$dateTime = new \DateTime('2024-03-25 14:30:45');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'date', 'd.m.Y');

		\Tester\Assert::same('25.03.2024', $value->getValue());
	}


	public function testDefaultFormatIsYmdHis(): void
	{
		$dateTime = new \DateTime('2024-12-31 23:59:59');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'timestamp');

		\Tester\Assert::same('2024-12-31 23:59:59', $value->getValue());
	}


	public function testDateOnlyFormat(): void
	{
		$dateTime = new \DateTime('2024-07-04');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'date', 'Y-m-d');

		\Tester\Assert::same('2024-07-04', $value->getValue());
	}


	public function testTimeOnlyFormat(): void
	{
		$dateTime = new \DateTime('2024-01-01 15:30:45');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'time', 'H:i:s');

		\Tester\Assert::same('15:30:45', $value->getValue());
	}


	public function testIsoFormat(): void
	{
		$dateTime = new \DateTime('2024-05-10 12:00:00', new \DateTimeZone('UTC'));
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'isoDate', 'c');

		\Tester\Assert::contains('2024-05-10T12:00:00', $value->getValue());
	}


	public function testImplementsValidationPropertyInterface(): void
	{
		$dateTime = new \DateTime();
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'field');

		\Tester\Assert::type(\Spameri\Elastic\Entity\Import\ValidationPropertyInterface::class, $value);
	}


	public function testIsReadonly(): void
	{
		$reflection = new \ReflectionClass(\Spameri\Elastic\Entity\Import\DateValue::class);

		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testGetValueReturnsString(): void
	{
		$dateTime = new \DateTime();
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'field');

		\Tester\Assert::type('string', $value->getValue());
	}


	public function testKeyReturnsString(): void
	{
		$dateTime = new \DateTime();
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'field');

		\Tester\Assert::type('string', $value->key());
	}


	public function testMidnightDate(): void
	{
		$dateTime = new \DateTime('2024-01-01 00:00:00');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'midnight');

		\Tester\Assert::same('2024-01-01 00:00:00', $value->getValue());
	}


	public function testEndOfDayDate(): void
	{
		$dateTime = new \DateTime('2024-12-31 23:59:59');
		$value = new \Spameri\Elastic\Entity\Import\DateValue($dateTime, 'endOfDay');

		\Tester\Assert::same('2024-12-31 23:59:59', $value->getValue());
	}

}

(new DateValueTest())->run();
