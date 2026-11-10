<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Lock;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class NullLockTest extends \Tester\TestCase
{

	public function testSetRunNameDoesNothing(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		// Should not throw, just silently do nothing
		$lock->setRunName('test-run');

		\Tester\Assert::true(true);
	}


	public function testAcquireAlwaysSucceeds(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		$result = $lock->acquire(60);

		\Tester\Assert::same($lock, $result);
	}


	public function testAcquireReturnsSelf(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		$result = $lock->acquire(3600);

		\Tester\Assert::type(\Spameri\Elastic\Import\LockInterface::class, $result);
		\Tester\Assert::same($lock, $result);
	}


	public function testMultipleAcquiresSucceed(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		// Simulate multiple concurrent imports - NullLock should always succeed
		$result1 = $lock->acquire(60);
		$result2 = $lock->acquire(60);
		$result3 = $lock->acquire(60);

		\Tester\Assert::same($lock, $result1);
		\Tester\Assert::same($lock, $result2);
		\Tester\Assert::same($lock, $result3);
	}


	public function testReleaseDoesNothing(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		$lock->acquire(60);
		$lock->release();

		// Should be able to acquire again without issues
		$result = $lock->acquire(60);

		\Tester\Assert::same($lock, $result);
	}


	public function testExtendDoesNothing(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		$lock->acquire(60);
		$lock->extend(120);

		// No exception should be thrown
		\Tester\Assert::true(true);
	}


	public function testImplementsLockInterface(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\NullLock();

		\Tester\Assert::type(\Spameri\Elastic\Import\LockInterface::class, $lock);
	}

}

(new NullLockTest())->run();
