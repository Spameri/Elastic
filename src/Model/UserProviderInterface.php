<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface UserProviderInterface
{

	public function getUser() : \Nette\Security\User;


	public function getId() : string;

}
