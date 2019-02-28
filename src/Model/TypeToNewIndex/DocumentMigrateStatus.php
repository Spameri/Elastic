<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\TypeToNewIndex;


class DocumentMigrateStatus
{

	/**
	 * @var array<string=>int>
	 */
	private $storage;


	public function add(
		string $documentId,
		int $version
	) : void
	{
		$this->storage[$documentId] = $version;
	}


	public function isChanged(
		string $documentId,
		int $version
	) : bool
	{
		if ( ! isset($this->storage[$documentId])) {
			return TRUE;
		}

		return $this->storage[$documentId] !== $version;
	}


	public function storage() : array
	{
		return $this->storage;
	}

}
