<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Property;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DateTimeTest extends \Tester\TestCase
{

	public function testCreateFromString(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-01-15 10:30:45');

		\Tester\Assert::same('2024-01-15T10:30:45', $dateTime->format());
	}


	public function testCreateFromDateTime(): void
	{
		$dt = new \DateTime('2024-06-20 14:30:00');
		$dateTime = \Spameri\Elastic\Entity\Property\DateTime::from($dt);

		\Tester\Assert::same('2024-06-20T14:30:00', $dateTime->format());
	}


	public function testFormatDefaultsToIso(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-03-25 08:15:30');

		\Tester\Assert::same('2024-03-25T08:15:30', $dateTime->format());
	}


	public function testFormatWithCustomFormat(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-12-31 23:59:59');

		\Tester\Assert::same('31.12.2024 23:59:59', $dateTime->format('d.m.Y H:i:s'));
	}


	public function testFormatConstant(): void
	{
		\Tester\Assert::same('Y-m-d\TH:i:s', \Spameri\Elastic\Entity\Property\DateTime::FORMAT);
	}


	public function testIndexFormatConstant(): void
	{
		\Tester\Assert::same('Y-m-d_H-i-s', \Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);
	}


	public function testExtendsNetteDateTime(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-01-01 00:00:00');

		\Tester\Assert::type(\Nette\Utils\DateTime::class, $dateTime);
	}


	public function testImplementsDateTimeInterface(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-01-01 00:00:00');

		\Tester\Assert::type(\Spameri\Elastic\Entity\DateTimeInterface::class, $dateTime);
	}


	public function testIncludesTimeComponent(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-05-10 16:45:30');
		$formatted = $dateTime->format();

		\Tester\Assert::contains('16:45:30', $formatted);
	}


	public function testFormatWithEmptyStringUsesDefault(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-08-15 12:00:00');

		\Tester\Assert::same('2024-08-15T12:00:00', $dateTime->format(''));
	}


	public function testFormatTimeAgoLessThanSecond(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('now');

		\Tester\Assert::same('less than 1 second ago', $dateTime->formatTimeAgo());
	}


	public function testFormatTimeAgoSeconds(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-30 seconds');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('second', $result);
	}


	public function testFormatTimeAgoMinutes(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-5 minutes');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('minute', $result);
	}


	public function testFormatTimeAgoHours(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-3 hours');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('hour', $result);
	}


	public function testFormatTimeAgoDays(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-2 days');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('day', $result);
	}


	public function testFormatTimeAgoMonths(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-2 months');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('month', $result);
	}


	public function testFormatTimeAgoYears(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-2 years');
		$result = $dateTime->formatTimeAgo();

		\Tester\Assert::contains('year', $result);
	}


	public function testFormatTimeAgoPluralForm(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('-1 day');
		$result1 = $dateTime->formatTimeAgo();

		$dateTime2 = new \Spameri\Elastic\Entity\Property\DateTime('-5 days');
		$result2 = $dateTime2->formatTimeAgo();

		// Single should not have 's'
		\Tester\Assert::contains('day ago', $result1);
		// Plural should have 's'
		\Tester\Assert::contains('days ago', $result2);
	}


	public function testMidnight(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-01-01 00:00:00');

		\Tester\Assert::same('2024-01-01T00:00:00', $dateTime->format());
	}


	public function testEndOfDay(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-12-31 23:59:59');

		\Tester\Assert::same('2024-12-31T23:59:59', $dateTime->format());
	}


	public function testIndexFormat(): void
	{
		$dateTime = new \Spameri\Elastic\Entity\Property\DateTime('2024-07-04 15:30:45');

		\Tester\Assert::same('2024-07-04_15-30-45', $dateTime->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT));
	}

}

(new DateTimeTest())->run();
