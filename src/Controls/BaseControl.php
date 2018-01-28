<?php

namespace Spameri\Elastic\Controls;


class BaseControl extends \Nette\Application\UI\Control
{

	/**
	 * @return \Nette\Application\UI\ITemplate|\Nette\Bridges\ApplicationLatte\Template
	 */
	public function getTemplate()
	{
		return parent::getTemplate();
	}


	/**
	 * @param bool $need
	 * @return \Nette\Application\UI\Presenter|\App\CoreModule\Presenters\BasePresenter
	 */
	public function getPresenter($need = TRUE)
	{
		return parent::getPresenter($need);
	}
}
