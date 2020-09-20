<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface LoggerHandlerInterface
{

	/**
	 * @param mixed $item
	 */
	public function logItemStart(
		$item
	): void;


	public function logPrepared(
		\Spameri\Elastic\Entity\AbstractImport $import
	): void;


	public function logResponse(
		\Spameri\Elastic\Import\ResponseInterface $result
	): void;


	public function logOmitException(
		\Spameri\Elastic\Import\Exception\ImportException $exception
	): void;


	public function logErrorException(
		\Spameri\Elastic\Import\Exception\ImportException $exception
	): void;


	public function logFatalException(
		\Spameri\Elastic\Import\Exception\ImportException $exception
	): void;


	public function logFinish(): void;

}
