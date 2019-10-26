<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import\RunHandler;

class NullHandler implements \Spameri\Elastic\Import\RunHandlerInterface
{

	public function advance(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void
	{
		// do nothing
	}


	public function finish(
		string $runName,
		\Symfony\Component\Console\Helper\ProgressBar $progressBar,
		?\Spameri\Elastic\Entity\AbstractImport $lastProcessed
	): void
	{
		// do nothing
	}

}
