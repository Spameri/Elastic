<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


interface IEntityFactory
{
	public function create(
		array $data
	);
}
