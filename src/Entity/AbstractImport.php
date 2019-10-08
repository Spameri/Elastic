<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

abstract class AbstractImport implements \Spameri\Elastic\Entity\Import\ValidationObjectInterface
{

	/**
	 * @var int|string
	 */
	private $key;


	public function __construct(
		$key
	)
	{
		$this->key = $key;
	}


	public function key()
	{
		return $this->key;
	}


	public function entityVariables(): array
	{
		$vars = \get_object_vars($this);
		unset($vars['key']);
		return $vars;
	}


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
