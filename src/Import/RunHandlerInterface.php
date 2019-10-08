<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface RunHandlerInterface
{

	public function advance(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void;


	public function finish(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void;

}
