<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Run;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class OptionsTest extends \Tester\TestCase
{

	public function testConstructorAcceptsLockDuration(): void
	{
		$options = new \Spameri\Elastic\Import\Run\Options(60);

		\Tester\Assert::type(\Spameri\Elastic\Import\Run\Options::class, $options);
	}


	public function testLockDurationReturnsCorrectValue(): void
	{
		$options = new \Spameri\Elastic\Import\Run\Options(120);

		\Tester\Assert::same(120, $options->lockDuration());
	}


	public function testDifferentLockDurations(): void
	{
		$options1 = new \Spameri\Elastic\Import\Run\Options(30);
		\Tester\Assert::same(30, $options1->lockDuration());

		$options2 = new \Spameri\Elastic\Import\Run\Options(3600);
		\Tester\Assert::same(3600, $options2->lockDuration());

		$options3 = new \Spameri\Elastic\Import\Run\Options(1);
		\Tester\Assert::same(1, $options3->lockDuration());
	}


	public function testZeroLockDuration(): void
	{
		$options = new \Spameri\Elastic\Import\Run\Options(0);

		\Tester\Assert::same(0, $options->lockDuration());
	}


	public function testNegativeLockDuration(): void
	{
		// The class doesn't validate, so negative values are allowed
		$options = new \Spameri\Elastic\Import\Run\Options(-1);

		\Tester\Assert::same(-1, $options->lockDuration());
	}


	public function testReadonlyProperty(): void
	{
		$options = new \Spameri\Elastic\Import\Run\Options(60);

		// Verify the class is readonly (reflection check)
		$reflection = new \ReflectionClass($options);
		\Tester\Assert::true($reflection->isReadOnly());
	}


	public function testLargeLockDuration(): void
	{
		// Test with large value (1 day in seconds)
		$options = new \Spameri\Elastic\Import\Run\Options(86400);

		\Tester\Assert::same(86400, $options->lockDuration());
	}

}

(new OptionsTest())->run();
