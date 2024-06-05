<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class DumpIndex
{

	private \Symfony\Component\Console\Output\OutputInterface $output;

	private string $bulkData;


	public function __construct(
		private readonly \Spameri\Elastic\Model\Scroll $scroll,
	)
	{
		$this->bulkData = '';
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output,
	): void
	{
		$this->output = $output;
	}


	public function execute(
		string $index,
		string $filename,
	): void
	{
		$continue = true;
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(5000);
		$elasticQuery->options()->startScroll('10m');

		$this->output->writeln('Starting dump.');
		$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output);
		$progressBar->setFormat('debug');

		\Nette\Utils\FileSystem::createDir(\dirname($filename));
		while ($continue) {
			$this->bulkData = '';
			$result = $this->scroll->execute($elasticQuery, $index);
			if ($progressBar->getMaxSteps() === 0) {
				$progressBar->start($result->stats()->total());
			}

			/** @var \Spameri\ElasticQuery\Response\Result\Hit $hit */
			foreach ($result->hits() as $hit) {
				$this->processHit($hit);
			}
			$progressBar->advance(5000);
			$this->writeToFile($filename);
			if ($result->stats()->total() <= $progressBar->getProgress()) {
				$continue = FALSE;
			}
		}

		$this->scroll->closeScroll($elasticQuery);

		$progressBar->finish();
		$this->output->writeln('Dump done and result is in file ' . $filename);
	}


	public function processHit(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
	): void
	{
		$bulkData = [
			'index' => [
				'_index' => $hit->index(),
				'_type' => $hit->type(),
				'_id' => $hit->id(),
			],
		];

		$this->bulkData .= \json_encode($bulkData) . "\r\n";
		$this->bulkData .= \json_encode($hit->source()) . "\r\n";
	}


	public function writeToFile(
		string $filename,
	): void
	{
		@\file_put_contents($filename, $this->bulkData, \FILE_APPEND);
	}

}
