<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Lock;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class FileLockTest extends \Tester\TestCase
{

	private string $lockDir;


	protected function setUp(): void
	{
		$this->lockDir = \TEMP_DIR . '/locks';
		\Nette\Utils\FileSystem::createDir($this->lockDir);
	}


	/**
	 * Helper to create an empty lock file (simulating no lock or expired lock)
	 */
	private function createEmptyLockFile(string $runName): void
	{
		\file_put_contents($this->lockDir . \DIRECTORY_SEPARATOR . $runName, '0');
	}


	protected function tearDown(): void
	{
		// Clean up any lock files
		\Nette\Utils\FileSystem::delete($this->lockDir);
	}


	public function testImplementsLockInterface(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);

		\Tester\Assert::type(\Spameri\Elastic\Import\LockInterface::class, $lock);
	}


	public function testSetRunNameSetsFileName(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('test-import');

		$expectedFileName = $this->lockDir . \DIRECTORY_SEPARATOR . 'test-import';

		\Tester\Assert::same($expectedFileName, $lock->getFileName());
	}


	public function testAcquireCreatesLockFile(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('acquire-test');

		// Create an empty/expired lock file first (FileLock expects file to exist)
		$this->createEmptyLockFile('acquire-test');

		$lock->acquire(60);

		\Tester\Assert::true(\file_exists($lock->getFileName()));

		// Clean up
		$lock->release();
	}


	public function testAcquireWritesExpireTime(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('expire-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('expire-test');

		$beforeTime = \time();
		$lock->acquire(60);
		$afterTime = \time();

		$lockContent = \file_get_contents($lock->getFileName());
		$expireTime = (int) $lockContent;

		// Expire time should be between now+60 (inclusive)
		\Tester\Assert::true($expireTime >= $beforeTime + 60);
		\Tester\Assert::true($expireTime <= $afterTime + 60);

		// Clean up
		$lock->release();
	}


	public function testAcquireReturnsSelf(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('return-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('return-test');

		$result = $lock->acquire(60);

		\Tester\Assert::same($lock, $result);

		// Clean up
		$lock->release();
	}


	public function testAcquireThrowsWhenAlreadyLocked(): void
	{
		$lock1 = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock1->setRunName('concurrent-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('concurrent-test');

		$lock1->acquire(60);

		$lock2 = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock2->setRunName('concurrent-test');

		\Tester\Assert::exception(
			static function () use ($lock2): void {
				$lock2->acquire(60);
			},
			\Spameri\Elastic\Import\Exception\Fatal::class,
			'Already locked.',
		);

		// Clean up
		$lock1->release();
	}


	public function testAcquireSucceedsWhenLockExpired(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('expired-test');

		// Create an expired lock file manually (time in the past)
		\file_put_contents($lock->getFileName(), (string) (\time() - 10));

		// Should succeed because lock is expired
		$result = $lock->acquire(60);

		\Tester\Assert::same($lock, $result);

		// Clean up
		$lock->release();
	}


	public function testReleaseDeletesLockFile(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('release-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('release-test');

		$lock->acquire(60);

		$fileName = $lock->getFileName();
		\Tester\Assert::true(\file_exists($fileName));

		$lock->release();

		\Tester\Assert::false(\file_exists($fileName));
	}


	public function testExtendUpdatesExpireTime(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('extend-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('extend-test');

		$lock->acquire(60);

		$initialExpire = (int) \file_get_contents($lock->getFileName());

		// Wait a tiny bit and extend
		\usleep(100000); // 0.1 seconds
		$lock->extend(120);

		$extendedExpire = (int) \file_get_contents($lock->getFileName());

		// Extended time should be greater than initial
		\Tester\Assert::true($extendedExpire > $initialExpire);

		// Clean up
		$lock->release();
	}


	public function testGetFileNameReturnsCorrectPath(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('my-import-run');

		$fileName = $lock->getFileName();

		\Tester\Assert::same($this->lockDir . \DIRECTORY_SEPARATOR . 'my-import-run', $fileName);
	}


	public function testCanReacquireAfterRelease(): void
	{
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);
		$lock->setRunName('reacquire-test');

		// Create an empty/expired lock file first
		$this->createEmptyLockFile('reacquire-test');

		$lock->acquire(60);
		$lock->release();

		// Create a new empty lock file (since release deleted it)
		$this->createEmptyLockFile('reacquire-test');

		// Should be able to acquire again
		$result = $lock->acquire(60);

		\Tester\Assert::same($lock, $result);

		// Clean up
		$lock->release();
	}

}

(new FileLockTest())->run();
