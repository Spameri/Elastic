<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class DeleteIndex
{

	public function __construct(
		private \Spameri\Elastic\Model\Indices\Get $get,
		private \Spameri\Elastic\Model\Indices\Delete $delete,
	)
	{
	}


	public function execute(string $index): void
	{
		try {
			$indexes = $this->get->execute($index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			return;
		}

		if ($indexes) {
			foreach ($indexes as $existingIndex) {
				$this->delete->execute($existingIndex['settings']['index']['provided_name']);
			}
		}
	}

}
