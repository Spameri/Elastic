<?php declare(strict_types=1);

namespace Spameri\Elastic\Model;


class RestoreIndex
{

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $output;

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output
	) : void
	{
		$this->output = $output;
	}

	public function execute(
		string $filename
		, int $step
	) : void
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
							[$bulkData]
						)
					)->toArray()
				);
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
