<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

class SimpleRun extends \Spameri\Elastic\Import\Run
{

	public function __construct(
		string $logDir,
		\Symfony\Component\Console\Output\NullOutput $output,
		\Spameri\Elastic\Import\Run\NullLoggerHandler $loggerHandler,
		\Spameri\Elastic\Import\Lock\NullLock $lock,
		\Spameri\Elastic\Import\RunHandler\NullHandler $runHandler,
		\Spameri\Elastic\Import\DataProviderInterface $dataProvider,
		\Spameri\Elastic\Import\PrepareImportDataInterface $prepareImportData,
		\Spameri\Elastic\Import\DataImportInterface $dataImport,
		\Spameri\Elastic\Import\AfterImport\NullAfterImport $afterImport
	)
	{
		parent::__construct($logDir, $output, $loggerHandler, $lock, $runHandler, $dataProvider, $prepareImportData, $dataImport, $afterImport);
	}

}
