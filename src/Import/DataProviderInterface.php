<?php declare(strict_types = 1);

namespace Spameri\Elastic\Import;

interface DataProviderInterface
{

	public function provide(
		\Spameri\Elastic\Import\Run\Options $options
	): \Generator;


	public function count(
		\Spameri\Elastic\Import\Run\Options $options
	): int;

}
