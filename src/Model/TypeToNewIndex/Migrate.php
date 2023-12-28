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
	 * @var \Spameri\Elastic\Provider\DateTimeProvider
	 */
	private $dateTimeProvider;

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
	 * @var \Spameri\Elastic\Model\Indices\Create
	 */
	private $create;

	/**
	 * @var \Spameri\Elastic\Model\Indices\Get
	 */
	private $indicesGet;

	private \Spameri\Elastic\Model\Indices\AddAlias $addAlias;

	private \Spameri\Elastic\Model\VersionProvider $versionProvider;


	public function __construct(
		DocumentMigrateStatus $documentMigrateStatus,
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider,
		\Spameri\Elastic\Model\Delete $delete,
		\Spameri\Elastic\Model\Get $get,
		\Spameri\Elastic\Model\Indices\Close $close,
		\Spameri\Elastic\Model\Indices\GetMapping $getMapping,
		\Spameri\Elastic\Model\Indices\PutMapping $putMapping,
		\Spameri\Elastic\Model\Search $search,
		\Spameri\Elastic\Model\Indices\Create $create,
		\Spameri\Elastic\Model\Indices\Get $indicesGet,
		\Spameri\Elastic\Model\Indices\AddAlias $addAlias,
		\Spameri\Elastic\Model\VersionProvider $versionProvider,
	)
	{
		$this->documentMigrateStatus = $documentMigrateStatus;
		$this->clientProvider = $clientProvider;
		$this->dateTimeProvider = $dateTimeProvider;
		$this->delete = $delete;
		$this->get = $get;
		$this->close = $close;
		$this->getMapping = $getMapping;
		$this->putMapping = $putMapping;
		$this->search = $search;
		$this->create = $create;
		$this->indicesGet = $indicesGet;
		$this->addAlias = $addAlias;
		$this->versionProvider = $versionProvider;
	}


	public function setOutput(
		\Symfony\Component\Console\Output\OutputInterface $output,
	): void
	{
		$this->output = $output;
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	public function execute(
		string $indexFrom,
		string $typeFrom,
		string $indexTo,
		string $aliasTo,
		string|null $typeTo,
		bool $allowClose,
	): void
	{
		// 1. Close index
		if ($allowClose) {
			$this->output->writeln('Closing index ' . $indexFrom);
			$this->close->execute($indexFrom);
		}

		// 2. Create new index
		$indexTo .= '_' . $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);
		$this->output->writeln('Creating index ' . $indexTo);

		// 2a. Put settings to new index
		$oldIndexSettings = $this->indicesGet->execute($indexFrom);
		$settings = \reset($oldIndexSettings);
		$indexToParameters = [];
		if (isset($settings['settings']['index']['analysis'])) {
			$indexToParameters = [
				'settings' => [
					'index' => $settings['settings']['index']['analysis'] ?? [],
				],
			];
		}
		$this->create->execute($indexTo, $indexToParameters);

		// 2b. Set mapping in new index
		$this->output->writeln('Transferring mapping from index: ' . $indexFrom . ' and type: ' . $typeFrom . ' to index: ' . $indexTo);
		$oldMapping = $this->getMapping->execute($indexFrom, $typeFrom);
		$firstMapping = \reset($oldMapping);
		$this->putMapping->execute($indexTo, $firstMapping['mappings']);

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
			/** @var \Spameri\ElasticQuery\Response\Result\Hit $response */
			foreach ($result->hits() as $response) {
				$this->processHit($indexTo, $typeTo, $indexFrom, $response, $allowClose);
			}

			if (\count($result->hits()->getIterator()) === 0) {
				$continue = FALSE;

			} else {
				$progressBar->advance(5000);
				$from += 5000;
				$elasticQuery->options()->changeFrom($from);
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
				// phpcs:ignore
				foreach ($this->documentMigrateStatus->storage() as $documentId => $documentVersion) {
					$response = $this->get->execute(
						new \Spameri\Elastic\Entity\Property\ElasticId((string) $documentId),
						$indexFrom,
						$typeFrom,
					);

					if ($this->documentMigrateStatus->isChanged((string) $documentId, $response->hit()->version())) {
						// Reindex this document
						$this->processHit($indexTo, $typeTo, $indexFrom, $response->hit(), $allowClose);
						$changed++;

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
		$this->addAlias->execute($aliasTo, $indexTo);

		// 9. Write info
		$this->output->writeln(
			'Migration done. All old data remains in old index: ' . $indexFrom . ' with type: ' . $typeFrom
			. ' it is recommended to manually delete data after this command',
		);

		// 10. Done
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	public function processHit(
		string $indexTo,
		string|null $typeTo,
		string $indexFrom,
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		bool $allowClose,
	): void
	{
		if ($this->versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$typeTo = NULL;
		}

		$this->clientProvider->client()->index(
			(
				new \Spameri\ElasticQuery\Document(
					$indexTo,
					new \Spameri\ElasticQuery\Document\Body\Plain($hit->source()),
					$typeTo,
					$hit->id(),
				)
			)->toArray(),
		)
		;

		if ($allowClose === FALSE) {
			$this->documentMigrateStatus->add($hit->id(), $hit->version());
		}

		if ($allowClose === TRUE) {
			$this->delete->execute(
				new \Spameri\Elastic\Entity\Property\ElasticId($hit->id()),
				$indexFrom,
			);
		}
	}

}
