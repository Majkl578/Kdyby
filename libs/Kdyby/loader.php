<?php

use Nette\Debug;
use Nette\Environment;


require_once LIBS_DIR . '/dump.php';


// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';


// Step 2: Load dibi
require LIBS_DIR . '/dibi/dibi.php';


/**
 * Load and configure F-CMS Kdyby
 */

define('KDYBY', TRUE);
define('KDYBY_DIR', __DIR__);


// Step 3: configuring Kdyby
// 3a) load AutoLoader & some other stuff
require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';
require_once KDYBY_DIR . '/shortcuts.php';

// 3b) Register Kdyby autoloader
\Kdyby\KdybyLoader::getInstance()->register();

// 3c) Override Nette Application Service
$locator = Environment::getServiceLocator();
$locator->addService('Nette\Application\Application', 'Kdyby\Application\Kdyby');


// Step 4: Configure Environment
// 4a) Enable Nette\Debug for better exception and error visualisation
Debug::enable();
Debug::$strictMode = True;
Debug::$maxDepth = 10;
Debug::$maxLen = 2024;

// 4b) Load configuration from app/config.ini file
Environment::loadConfig(APP_DIR.'/config.ini');


// Step 6: Application
// 4a) Get front controller
$application = Environment::getApplication();
