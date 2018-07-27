<?php

namespace Spameri\Elastic\Model;


class UserProvider
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
}
