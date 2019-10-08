<?php declare(strict_types = 1);

namespace Spameri\Elastic\Migration;

class MigrationRunner
{

	public function iterate(): void
	{
		/** @var \Spameri\Elastic\Migration\MigrationFileInterface $file */
		foreach ($files as $file) {
			$this->run($file);
		}
	}


	public function run(
		MigrationFileInterface $file
	): void
	{
		$file->before();
		$file->migrate();
		$file->after();
	}

}
