<?php


namespace Kdyby\Application;

use Nette\Environment;


/**
 * Description of Kdyby
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
final class Kdyby extends \Nette\Application\Application
{

	/** @var array of function(Application $sender); Occurs before the application loads itself */
	public $onLoad;

	/** @var bool */
	private $hooked = FALSE;


	//public $errorPresenter = 'Error';
	
	//public $catchExceptions = TRUE;



	public function hook()
	{
		$this->onLoad[] = callback($this, 'hookServices');

		$this->onLoad[] = callback($this, 'hookFillLoader');

		$this->hooked = TRUE;
	}


	public function run()
	{
		if( !$this->hooked ){
			throw new \InvalidStateException("Call \$application->hook(); first!");
		}

		$this->onLoad($this);

		parent::run();
	}


	public function getLoader()
	{
		return \Kdyby\KdybyLoader::getInstance();
	}


	public function hookFillLoader()
	{
		$this->getLoader()->loadCache();
	}


	public function hookServices()
	{
		$locator = $this->getServiceLocator();

		// Nette\Application\IRouter override
		$locator->addService('Nette\Application\IRouter', 'Kdyby\Application\ExtendableRouter');

		// Nette\Security\IAuthenticator
		//$locator->addService("Nette\Security\IAuthenticator", "");
	}


	public static function createPresenterLoader()
	{
		// Kdyby\Application\PresenterLoader
		return new PresenterLoader($this->getLoader(), Environment::getVariable('appDir'));
	}

}
