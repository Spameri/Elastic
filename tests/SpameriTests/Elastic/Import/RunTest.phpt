<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class RunTest extends \Tester\TestCase
{

	private string $logDir;

	private string $lockDir;


	protected function setUp(): void
	{
		$this->logDir = \TEMP_DIR . '/logs';
		$this->lockDir = \TEMP_DIR . '/locks';
		\Nette\Utils\FileSystem::createDir($this->logDir);
		\Nette\Utils\FileSystem::createDir($this->lockDir);
	}


	protected function tearDown(): void
	{
		\Nette\Utils\FileSystem::delete($this->logDir);
		\Nette\Utils\FileSystem::delete($this->lockDir);
	}


	private function createRun(
		\Spameri\Elastic\Import\DataProviderInterface $dataProvider,
		\Spameri\Elastic\Import\PrepareImportDataInterface $prepareImportData,
		\Spameri\Elastic\Import\DataImportInterface $dataImport,
		\Spameri\Elastic\Import\LoggerHandlerInterface|null $loggerHandler = null,
		\Spameri\Elastic\Import\LockInterface|null $lock = null,
		\Spameri\Elastic\Import\AfterImportInterface|null $afterImport = null,
	): \Spameri\Elastic\Import\Run {
		return new \Spameri\Elastic\Import\Run(
			$this->logDir,
			$loggerHandler ?? new \Spameri\Elastic\Import\Run\NullLoggerHandler(),
			$lock ?? new \Spameri\Elastic\Import\Lock\NullLock(),
			new \Spameri\Elastic\Import\RunHandler\NullHandler(),
			$dataProvider,
			$prepareImportData,
			$dataImport,
			$afterImport ?? new \Spameri\Elastic\Import\AfterImport\NullAfterImport(),
		);
	}


	public function testFullImportCycle(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => 'item1', 'name' => 'First', 'value' => 100],
			['key' => 'item2', 'name' => 'Second', 'value' => 200],
			['key' => 'item3', 'name' => 'Third', 'value' => 300],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		$afterImport = new \SpameriTests\Elastic\Data\Import\MockAfterImport();
		$loggerHandler = new \SpameriTests\Elastic\Data\Import\MockLoggerHandler();

		$run = $this->createRun(
			$dataProvider,
			$prepareImportData,
			$dataImport,
			$loggerHandler,
			afterImport: $afterImport,
		);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Verify all items were processed
		\Tester\Assert::same(3, $prepareImportData->getCallCount());
		\Tester\Assert::same(3, $dataImport->getCallCount());
		\Tester\Assert::same(3, $afterImport->getCallCount());

		// Verify logging
		\Tester\Assert::same(3, \count($loggerHandler->getItemsStarted()));
		\Tester\Assert::same(3, \count($loggerHandler->getPreparedEntities()));
		\Tester\Assert::same(3, \count($loggerHandler->getResponses()));
		\Tester\Assert::true($loggerHandler->isFinishCalled());
	}


	public function testProgressTracking(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'One', 'value' => 1],
			['key' => '2', 'name' => 'Two', 'value' => 2],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Verify count was called correctly
		\Tester\Assert::same(2, $dataProvider->count($options));
	}


	public function testOmitExceptionHandling(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'One', 'value' => 1],
			['key' => '2', 'name' => 'Two', 'value' => 2],
			['key' => '3', 'name' => 'Three', 'value' => 3],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		// Throw Omit exception on second item
		$prepareImportData->setExceptionForCall(1, new \Spameri\Elastic\Import\Exception\Omit('Skipping item'));

		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		$loggerHandler = new \SpameriTests\Elastic\Data\Import\MockLoggerHandler();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, $loggerHandler);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Should continue processing other items
		\Tester\Assert::same(3, $prepareImportData->getCallCount());
		\Tester\Assert::same(2, $dataImport->getCallCount()); // Only 2 items imported (1 omitted)
		\Tester\Assert::same(1, \count($loggerHandler->getOmitExceptions()));
		\Tester\Assert::true($loggerHandler->isFinishCalled());
	}


	public function testErrorExceptionHandling(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'One', 'value' => 1],
			['key' => '2', 'name' => 'Two', 'value' => 2],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		// Throw Error exception on first import
		$dataImport->setExceptionForCall(0, new \Spameri\Elastic\Import\Exception\Error('API error'));

		$loggerHandler = new \SpameriTests\Elastic\Data\Import\MockLoggerHandler();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, $loggerHandler);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Should continue processing
		\Tester\Assert::same(2, $prepareImportData->getCallCount());
		\Tester\Assert::same(1, \count($loggerHandler->getErrorExceptions()));
		\Tester\Assert::true($loggerHandler->isFinishCalled());
	}


	public function testFatalExceptionHandling(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'One', 'value' => 1],
			['key' => '2', 'name' => 'Two', 'value' => 2],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		// Throw Fatal exception on first import
		$dataImport->setExceptionForCall(0, new \Spameri\Elastic\Import\Exception\Fatal('Critical error'));

		$loggerHandler = new \SpameriTests\Elastic\Data\Import\MockLoggerHandler();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, $loggerHandler);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Fatal exception is caught but logged - import continues
		\Tester\Assert::same(1, \count($loggerHandler->getFatalExceptions()));
		\Tester\Assert::true($loggerHandler->isFinishCalled());
	}


	public function testAfterImportHooksCalledWithCorrectData(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => 'test1', 'name' => 'Test Item', 'value' => 999],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		$afterImport = new \SpameriTests\Elastic\Data\Import\MockAfterImport();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, afterImport: $afterImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		$processedItems = $afterImport->getProcessedItems();

		\Tester\Assert::same(1, \count($processedItems));
		\Tester\Assert::same(['key' => 'test1', 'name' => 'Test Item', 'value' => 999], $processedItems[0]['entityData']);
		\Tester\Assert::true($processedItems[0]['response']->isSuccessful());
	}


	public function testEmptyDataProvider(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();
		$loggerHandler = new \SpameriTests\Elastic\Data\Import\MockLoggerHandler();

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, $loggerHandler);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Should complete without errors
		\Tester\Assert::same(0, $prepareImportData->getCallCount());
		\Tester\Assert::same(0, $dataImport->getCallCount());
		\Tester\Assert::true($loggerHandler->isFinishCalled());
	}


	public function testLockIsAcquiredAndReleased(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([['key' => '1', 'name' => 'One', 'value' => 1]]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		// Create a file-based lock for testing
		$lock = new \Spameri\Elastic\Import\Lock\FileLock($this->lockDir);

		// Create empty lock file
		\file_put_contents($this->lockDir . \DIRECTORY_SEPARATOR . 'Run', '0');

		$run = $this->createRun($dataProvider, $prepareImportData, $dataImport, lock: $lock);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$run->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$run->execute($options);

		// Lock file should be released (deleted) after run
		\Tester\Assert::false(\file_exists($this->lockDir . \DIRECTORY_SEPARATOR . 'Run'));
	}

}

(new RunTest())->run();
