<?php declare(strict_types = 1);

namespace Spameri\Elastic\EventManager;

interface ListenerInterface
{

	public function handle(
		object|null $entity,
		object|null $parent,
	): void;

	/**
	 * @return array<class-string>
	 */
	public function getEntityClass(): array;


	public function getEvent(): string;

}
