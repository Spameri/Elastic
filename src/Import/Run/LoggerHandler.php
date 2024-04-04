<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Run;

readonly class LoggerHandler implements \Spameri\Elastic\Import\LoggerHandlerInterface
{

	public function __construct(
		private \Psr\Log\LoggerInterface $logger,
	)
	{
	}


	/**
	 * @param mixed $item
	 */
	public function logItemStart($item): void
	{
		$this->logger->debug('Processing item ' . \Tracy\Dumper::toText($item));
	}


	public function logPrepared(\Spameri\Elastic\Entity\AbstractImport $import): void
	{
		$this->logger->debug('Prepared data ' . \Tracy\Dumper::toText($import->toArray()));
	}


	public function logResponse(\Spameri\Elastic\Import\ResponseInterface $result): void
	{
		$this->logger->debug('Response ' . \Tracy\Dumper::toText($result));
	}


	public function logOmitException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->logger->notice($exception->getMessage());
	}


	public function logErrorException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->logger->error($exception->getMessage());
	}


	public function logFatalException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		$this->logger->critical($exception->getMessage());
	}


	public function logFinish(): void
	{
		$this->logger->info('Finished');
	}

}
