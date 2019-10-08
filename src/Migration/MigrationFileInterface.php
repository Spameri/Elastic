<?php declare(strict_types = 1);

namespace Spameri\Elastic\Migration;


interface MigrationFileInterface
{

	public function before(): void;

	public function migrate(): void;

	public function after(): void;

}
