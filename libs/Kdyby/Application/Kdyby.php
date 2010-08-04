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

	/** @var array of function(Application $sender); Occurs before the application loads itself */
//	public $onDebugPanels;

	/** @var bool */
	private $hooked = FALSE;


	//public $errorPresenter = 'Error';
	
	//public $catchExceptions = TRUE;



	public function hook()
	{
		// hooks aditional services
		$this->onLoad[] = callback($this, 'hookServices');

		// fills loader with cached data
		$this->onLoad[] = callback($this, 'hookFillLoader');

		// sets few default extendable routes
		$this->onLoad[] = callback($this, 'createDefaultRoutes');

		// save new patterns whether becomed avalaible during new modifications loading
		$this->onShutdown[] = callback($this, 'invalidateRoutes');

		// we can run!
		$this->hooked = TRUE;
	}


	public function run()
	{
		if( !$this->hooked ){
			throw new \InvalidStateException("Call \$application->hook(); first!");
		}

//		if( !Environment::isProduction() ){
//			$this->onDebugPanels($this);
//		}

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


	public function invalidateRoutes()
	{
		$this->getRouter()->invalidateRoutes();
	}


	public function createDefaultRoutes()
	{
		$router = $this->getRouter();

		if( count($router) >= 4 ){
			return;
		}

		$router->extend('node', '/<node>/<action>', array(
		    'action' => Null
		));

		$router->extend('langNode', '/<language>/<node>/<action>', array(
		    'language' => 'cz',
		    'action' => Null
		));

		$router->extend('section', '/<section>/<node>/<action>', array(
		    'language' => 'cz',
		    'action' => Null
		));

		$router->extend('langSection', '/<language>/<section>/<node>/<action>', array(
		    'language' => 'cz',
		    'action' => Null
		));

		$router->invalidateRoutes();
	}


	public static function createPresenterLoader()
	{
		// Kdyby\Application\PresenterLoader
		return new PresenterLoader($this->getLoader(), Environment::getVariable('appDir'));
	}

}
