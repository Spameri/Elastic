<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Entity\Property;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DateTest extends \Tester\TestCase
{

	public function testCreateFromString(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-01-15');

		\Tester\Assert::same('2024-01-15', $date->format());
	}


	public function testCreateFromDateTime(): void
	{
		$dateTime = new \DateTime('2024-06-20 14:30:00');
		$date = \Spameri\Elastic\Entity\Property\Date::from($dateTime);

		\Tester\Assert::same('2024-06-20', $date->format());
	}


	public function testFormatDefaultsToYmd(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-03-25');

		\Tester\Assert::same('2024-03-25', $date->format());
	}


	public function testFormatWithCustomFormat(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-12-31');

		\Tester\Assert::same('31.12.2024', $date->format('d.m.Y'));
	}


	public function testFormatConstant(): void
	{
		\Tester\Assert::same('Y-m-d', \Spameri\Elastic\Entity\Property\Date::FORMAT);
	}


	public function testExtendsNetteDateTime(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-01-01');

		\Tester\Assert::type(\Nette\Utils\DateTime::class, $date);
	}


	public function testImplementsDateTimeInterface(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-01-01');

		\Tester\Assert::type(\Spameri\Elastic\Entity\DateTimeInterface::class, $date);
	}


	public function testTimezoneHandling(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-07-04', new \DateTimeZone('UTC'));

		\Tester\Assert::same('2024-07-04', $date->format());
	}


	public function testFormatWithEmptyStringUsesDefault(): void
	{
		$date = new \Spameri\Elastic\Entity\Property\Date('2024-08-15');

		\Tester\Assert::same('2024-08-15', $date->format(''));
	}


	public function testDifferentDateFormatsInput(): void
	{
		// ISO format
		$date1 = new \Spameri\Elastic\Entity\Property\Date('2024-05-10');
		\Tester\Assert::same('2024-05-10', $date1->format());

		// From timestamp
		$date2 = \Spameri\Elastic\Entity\Property\Date::from(new \DateTime('@1715356800')); // 2024-05-10
		\Tester\Assert::same('2024-05-10', $date2->format());
	}


	public function testYearBoundaries(): void
	{
		$startOfYear = new \Spameri\Elastic\Entity\Property\Date('2024-01-01');
		$endOfYear = new \Spameri\Elastic\Entity\Property\Date('2024-12-31');

		\Tester\Assert::same('2024-01-01', $startOfYear->format());
		\Tester\Assert::same('2024-12-31', $endOfYear->format());
	}

}

(new DateTest())->run();
