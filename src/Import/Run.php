<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

class Run
{

	private \Symfony\Component\Console\Output\OutputInterface $output;

	protected string $runName;

	protected string $fileName;

	private \Symfony\Component\Console\Helper\ProgressBar $progressBar;


	/**
	 * @throws \ReflectionException
	 */
	public function __construct(
		protected string $logDir,
		private readonly \Spameri\Elastic\Import\LoggerHandlerInterface $loggerHandler,
		private readonly \Spameri\Elastic\Import\LockInterface $lock,
		private readonly \Spameri\Elastic\Import\RunHandlerInterface $runHandler,
		private readonly \Spameri\Elastic\Import\DataProviderInterface $dataProvider,
		private readonly \Spameri\Elastic\Import\PrepareImportDataInterface $prepareImportData,
		private readonly \Spameri\Elastic\Import\DataImportInterface $dataImport,
		private readonly \Spameri\Elastic\Import\AfterImportInterface $afterImport,
	)
	{
		$this->runName = (new \ReflectionClass($this))->getShortName();
		$this->lock->setRunName($this->runName);
		$this->setUpLogger($logDir);
	}


	public function setOutput(\Symfony\Component\Console\Output\OutputInterface $output): void
	{
		$this->output = $output;
	}


	protected function setUpLogger(string $logDir): void
	{
		$directory = $logDir;
		\Nette\Utils\FileSystem::createDir($directory);

		$directory .= \DIRECTORY_SEPARATOR;
		$directory .= $this->runName;
		\Nette\Utils\FileSystem::createDir($directory);

		$directory .= \DIRECTORY_SEPARATOR;
		$directory .= (new \DateTime())->format('Y-m-d');
		\Nette\Utils\FileSystem::createDir($directory);

		$this->fileName = (new \DateTime())->format('H-i-s');
	}


	public function execute(
		\Spameri\Elastic\Import\Run\Options $options,
	): void
	{
		$this->lock->acquire($options->lockDuration());

		$this->initializeProgressBar($this->dataProvider->count($options));

		$data = $this->dataProvider->provide($options);

		foreach ($data as $item) {
			try {
				$this->loggerHandler->logItemStart($item);

				$prepared = $this->prepareImportData->prepare($item);
				$this->loggerHandler->logPrepared($prepared);

				$response = $this->dataImport->import($prepared);
				$this->loggerHandler->logResponse($response);

				$this->afterImport->process($item, $response);

				$this->runHandler->advance($this->runName, $this->progressBar, $prepared);

				$this->lock->extend($options->lockDuration());

			} catch (\Spameri\Elastic\Import\Exception\Omit $exception) {
				$this->loggerHandler->logOmitException($exception);

			} catch (\Spameri\Elastic\Import\Exception\Error $exception) {
				$this->loggerHandler->logErrorException($exception);

			} catch (\Spameri\Elastic\Import\Exception\Fatal $exception) {
				$this->loggerHandler->logFatalException($exception);
			}
		}

		$this->runHandler->finish($this->runName, $this->progressBar, $prepared ?? NULL);

		$this->lock->release();

		$this->loggerHandler->logFinish();
	}


	public function initializeProgressBar(int $maxCount): void
	{
		$this->progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output, $maxCount);
		$this->progressBar->setFormat('debug');
		$this->progressBar->start($maxCount);
	}

}
