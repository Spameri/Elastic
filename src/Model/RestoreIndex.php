<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class RestoreIndex
{

	private \Symfony\Component\Console\Output\OutputInterface $output;


	public function __construct(
		private readonly \Spameri\Elastic\ClientProvider $clientProvider,
	)
	{
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output,
	): void
	{
		$this->output = $output;
	}


	public function execute(
		string $filename,
		int $step,
	): void
	{
		$this->output->writeln('Starting import.');
		$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output);
		$progressBar->setFormat('debug');

		$bulkData = '';
		$i = 0;
		$file = \fopen($filename, 'rb');
		while ( ! \feof($file)) {
			$bulkData .= \fgets($file);
			$i++;

			if ($i === $step || \feof($file)) {
				$this->clientProvider->client()->bulk(
					(
						new \Spameri\ElasticQuery\Document\Bulk(
							[$bulkData],
						)
					)->toArray(),
				)
				;
				$bulkData = '';
				$i = 0;
				$progressBar->advance($step / 2);
			}
		}
		\fclose($file);

		$progressBar->finish();
		$this->output->writeln('Import done.');
	}

}
