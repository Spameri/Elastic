<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\RunHandler;

class ConsoleHandler implements \Spameri\Elastic\Import\RunHandlerInterface
{

	public function advance(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void
	{
		$progressBar->advance();
		if ($progressBar->getProgress() % 100) {
			$progressBar->display();
		}
	}


	public function finish(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void
	{
		$progressBar->finish();
	}

}
