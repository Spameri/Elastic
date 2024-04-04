<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

abstract readonly class AbstractImport implements \Spameri\Elastic\Entity\Import\ValidationObjectInterface
{

	public function __construct(
		private int|string $key,
	)
	{
	}


	public function key(): int|string
	{
		return $this->key;
	}


	/**
	 * @return array<mixed>
	 */
	public function entityVariables(): array
	{
		$vars = \get_object_vars($this);
		unset($vars['key']);

		return $vars;
	}


	/**
	 * @return array<mixed>
	 */
	public function toArray(): array
	{
		$array = [];

		foreach ($this->entityVariables() as $key => $variable) {
			if ($variable instanceof \Spameri\Elastic\Entity\Import\NoValue) {
				continue;
			}

			if ($variable instanceof \Spameri\Elastic\Entity\Import\ValidationPropertyInterface) {
				$array[$variable->key()] = $variable->getValue();

			} elseif ($variable instanceof \Spameri\Elastic\Entity\Import\ValidationObjectInterface) {
				$array[$variable->key()] = $variable->toArray();

			} else {
				$array[$key] = $variable;
			}
		}

		return $array;
	}

}
