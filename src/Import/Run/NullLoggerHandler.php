<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\Run;

class NullLoggerHandler implements \Spameri\Elastic\Import\LoggerHandlerInterface
{

	/**
	 * @param mixed $item
	 */
	public function logItemStart($item): void
	{
		// do nothing;
	}


	public function logPrepared(\Spameri\Elastic\Entity\AbstractImport $import): void
	{
		// do nothing;
	}


	public function logResponse(\Spameri\Elastic\Import\ResponseInterface $result): void
	{
		// do nothing
	}


	public function logOmitException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		// do nothing
	}


	public function logErrorException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		// do nothing
	}


	public function logFatalException(\Spameri\Elastic\Import\Exception\ImportException $exception): void
	{
		// do nothing
	}


	public function logFinish(): void
	{
		// do nothing
	}

}
