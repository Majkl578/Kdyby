<?php

use Nette\Debug;
use Nette\Environment;


// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';


// Step 2: Load dibi
require LIBS_DIR . '/dibi/dibi.php';


if( constant("NETTE") !== TRUE ){
	throw new \Exception("Nette initialization is first!");
}



/**
 * Load and configure F-CMS Kdyby
 */

define('KDYBY', TRUE);
define('KDYBY_DIR', __DIR__);


// Autoloader for classes
require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';

\Kdyby\KdybyLoader::getInstance()->register();



// Step : Override Nette services
$locator = Environment::getServiceLocator();

// Nette\Application\Application override
$locator->addService('Nette\Application\Application', 'Kdyby\Application\Kdyby');

// Nette\Application\IRouter override
$locator->addService('Nette\Application\IRouter', 'Kdyby\Application\ExtendableRouter');

// Nette\Security\IAuthenticator
//$locator->addService("Nette\Security\IAuthenticator", "");



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();
Debug::$strictMode = True;

// 2b) load configuration from config.ini file
Environment::loadConfig();

// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;



$application->register();

// Step : Run the application!
$application->run();