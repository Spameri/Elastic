<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\TypeToNewIndex;


class Migrate
{

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface $output
	 */
	private $output;

	/**
	 * @var \Spameri\Elastic\Model\TypeToNewIndex\DocumentMigrateStatus
	 */
	private $documentMigrateStatus;

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	/**
	 * @var \Kdyby\DateTimeProvider\Provider\ConstantProvider
	 */
	private $constantProvider;

	/**
	 * @var \Spameri\Elastic\Model\Delete
	 */
	private $delete;

	/**
	 * @var \Spameri\Elastic\Model\Get
	 */
	private $get;

	/**
	 * @var \Spameri\Elastic\Model\Indices\Close
	 */
	private $close;

	/**
	 * @var \Spameri\Elastic\Model\Indices\GetMapping
	 */
	private $getMapping;

	/**
	 * @var \Spameri\Elastic\Model\Indices\PutMapping
	 */
	private $putMapping;

	/**
	 * @var \Spameri\Elastic\Model\Search
	 */
	private $search;

	/**
	 * @var \Spameri\Elastic\Mapper\ElasticMapper
	 */
	private $elasticMapper;

	/**
	 * @var \Spameri\Elastic\Model\Indices\Create
	 */
	private $create;


	public function __construct(
		DocumentMigrateStatus $documentMigrateStatus
		, \Spameri\Elastic\ClientProvider $clientProvider
		, \Kdyby\DateTimeProvider\Provider\ConstantProvider $constantProvider
		, \Spameri\Elastic\Model\Delete $delete
		, \Spameri\Elastic\Model\Get $get
		, \Spameri\Elastic\Model\Indices\Close $close
		, \Spameri\Elastic\Model\Indices\GetMapping $getMapping
		, \Spameri\Elastic\Model\Indices\PutMapping $putMapping
		, \Spameri\Elastic\Model\Search $search
		, \Spameri\Elastic\Mapper\ElasticMapper $elasticMapper
		, \Spameri\Elastic\Model\Indices\Create $create
	)
	{
		$this->documentMigrateStatus = $documentMigrateStatus;
		$this->clientProvider = $clientProvider;
		$this->constantProvider = $constantProvider;
		$this->delete = $delete;
		$this->get = $get;
		$this->close = $close;
		$this->getMapping = $getMapping;
		$this->putMapping = $putMapping;
		$this->search = $search;
		$this->elasticMapper = $elasticMapper;
		$this->create = $create;
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output
	) : void
	{
		$this->output = $output;
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	public function execute(
		string $indexFrom
		, string $typeFrom
		, string $indexTo
		, string $aliasTo
		, bool $allowClose
	) : void
	{
		// 1. Close index
		$this->output->writeln('Closing index ' . $indexFrom);
		$this->close->execute($indexFrom);

		// 2. Create new index
		$indexTo .= $this->constantProvider->getDateTime()->format('Y-m-d_H-i-s');
		$this->output->writeln('Creating index ' . $indexTo);
		$this->create->execute($indexTo);

		// 2a. Set mapping in new index
		$this->output->writeln('Transferring mapping from index: ' . $indexFrom . ' and type: ' . $typeFrom . ' to index: ' . $indexTo);
		$oldMapping = $this->getMapping->execute($indexFrom, $typeFrom);
		$this->putMapping->execute($indexTo, $oldMapping);

		// 3. Foreach index data
		$this->output->writeln('Starting migration.');
		$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output);
		$progressBar->setFormat('debug');

		$continue = TRUE;
		$from = 0;
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(5000);
		while ($continue) {
			$result = $this->search->execute($elasticQuery, $indexFrom, $typeFrom);

			// 4. Input data to new index
			// 4a. if closed delete data
			// 4b. if open store migrated version
			/** @var \Spameri\ElasticQuery\Response\Result\Hit $hit */
			foreach ($result as $hit) {
				$this->processHit($indexTo, $indexFrom, $hit, $allowClose);

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
		// 5. loop end

		// 6. If open transfer again changed docs
		// 7. Apply previous step until empty queue or 10 loops
		if ($allowClose === FALSE) {
			$this->output->writeln('Starting update changed documents');
			$updateBar = new \Symfony\Component\Console\Helper\ProgressBar($this->output);
			$updateBar->setFormat('debug');

			$canContinue = TRUE;
			$loops = 0;
			while ($canContinue) {
				$changed = 0;
				foreach ($this->documentMigrateStatus->storage() as $documentId => $documentVersion) {
					$hit = $this->get->execute(
						new \Spameri\Elastic\Entity\Property\ElasticId($documentId),
						$indexFrom,
						$typeFrom
					);

					if ($this->documentMigrateStatus->isChanged($documentId, $hit->version())) {
						// Reindex this document
						$this->processHit($indexTo, $indexFrom, $hit, $allowClose);
						$changed++;

						/** @noinspection DisconnectedForeachInstructionInspection */
						$updateBar->advance();
					}
				}

				$loops++;
				if ($loops >= 10) {
					$canContinue = FALSE;
					$updateBar->finish();
					$this->output->writeln('Loops limit reached, data is too frequently updated, please keep in mind there can be inconsistencies after this command.');
				}
				if ($changed === 0) {
					$canContinue = FALSE;
					$updateBar->finish();
					$this->output->writeln('Documents changed during migrate reindexed.');
				}
			}
		}

		// 8. Switch to new index
		$this->output->writeln('Adding alias: ' . $aliasTo . ' to index: ' . $indexTo);
		$this->elasticMapper->addAlias($indexTo, $aliasTo);

		// 9. Write info
		$this->output->writeln(
			'Migration done. All old data remains in old index: '. $indexFrom . ' with type: ' . $typeFrom
			. ' it is recommended to manually delete data after this command'
		);

		// 10. Done
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	public function processHit(
		string $indexTo
		, string $indexFrom
		, \Spameri\ElasticQuery\Response\Result\Hit $hit
		, bool $allowClose
	) : void
	{
		$document = new \Spameri\ElasticQuery\Document(
			$indexTo,
			new \Spameri\ElasticQuery\Document\Body\Plain($hit->source()),
			$indexTo,
			$hit->id()
		);

		$this->clientProvider->client()->index($document->toArray());

		if ($allowClose === FALSE) {
			$this->documentMigrateStatus->add($hit->id(), $hit->version());
		}

		if ($allowClose === TRUE) {
			$this->delete->execute(
				new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
				$indexFrom
			);
		}
	}

}
