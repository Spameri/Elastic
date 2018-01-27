<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


/**
 * BEWARE!
 * Base entity is for frontend use only.
 * It is supposed to be fast but not reliable.
 * It is not advised to modify/save this entity directly in your application.
 */
abstract class BaseEntity implements IBaseEntity
{

	/**
	 * @var array
	 */
	protected $metadata;


	public function __construct(array $data)
	{
		$this->metadata = $data;
	}


	public function metadata() : array
	{
		return $this->metadata;
	}


	public function toArray() : array
	{
		$array = [];

		foreach (get_object_vars($this) as $name => $value) {
			/** @var $value BaseEntity */
			if (is_object($value)) {
				$array[$name] = $value->toArray();
			} else {
				$array[$name] = $value;
			}
		}

		return $array;
	}
}
