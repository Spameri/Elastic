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


	/**
	 * @return \Nette\Security\User
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * @return \Nette\Security\IIdentity|NULL
	 */
	public function getIdentity()
	{
		return $this->user->getIdentity();
	}
}