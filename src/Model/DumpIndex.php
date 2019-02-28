<?php declare(strict_types=1);

namespace Spameri\Elastic\Model;


class DumpIndex
{

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $output;

	/**
	 * @var \Spameri\Elastic\Model\Search
	 */
	private $search;


	public function __construct(
		Search $search
	)
	{
		$this->search = $search;
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output
	) : void
	{
		$this->output = $output;
	}

	public function execute(
		string $index
		, string $filename
	) : void
	{
		$from = 0;
		$continue = TRUE;
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(5000);

		$this->output->writeln('Starting dump.');
		$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output);
		$progressBar->setFormat('debug');

		\Nette\Utils\FileSystem::createDir(dirname($filename));
		while ($continue) {
			$result = $this->search->execute($elasticQuery, $index);

			/** @var \Spameri\ElasticQuery\Response\Result\Hit $hit */
			foreach ($result as $hit) {
				$this->processHit($hit, $filename);

				/** @noinspection DisconnectedForeachInstructionInspection */
				$progressBar->advance();
			}
			$from += 5000;
			$elasticQuery->options()->changeFrom($from);
			if ($result->stats()->total() === 0) {
				$continue = FALSE;
			}
		}
		$progressBar->finish();
		$this->output->writeln('Dump done and result is in file ' . $filename);
	}


	public function processHit(
		\Spameri\ElasticQuery\Response\Result\Hit $hit
		, string $filename
	) : void
	{
		$bulkData = [
			'index' => [
				'_index'	=> $hit->index(),
				'_type'  	=> $hit->type(),
				'_id'  		=> $hit->id(),
			],
		];

		@file_put_contents($filename, \Nette\Utils\Json::encode($bulkData) . "\r\n", \FILE_APPEND);
		@file_put_contents($filename, \Nette\Utils\Json::encode($hit->source()) . "\r\n", \FILE_APPEND);
	}

}
