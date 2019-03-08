<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class NetteUserProvider implements UserProviderInterface
{

	/**
	 * @var \Nette\Security\User
	 */
	private $user;


	public function __construct(
		\Nette\Security\User $user
	)
	{
		$this->user = $user;
	}


	public function getUser() : \Nette\Security\User
	{
		return $this->user;
	}


	public function getIdentity() : ?\Nette\Security\IIdentity
	{
		return $this->user->getIdentity();
	}


	public function getId(): string
	{
		$id = '';
		if ($this->user->getIdentity()) {
			/** @var mixed $id */
			$id = $this->user->getIdentity()->getId() ?: '';
		}

		return (string) $id;
	}

}
