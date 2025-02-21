<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

interface EntityInterface
{

	public function key(): string;


	/**
	 * @return array<string, mixed>
	 */
	public function entityVariables(): array;

}
