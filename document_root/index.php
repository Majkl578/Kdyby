<?php

// absolute filesystem path to the web root
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');

// load bootstrap file
require LIBS_DIR . '/Kdyby/loader.php';

// 6b) Hook callbacks
$application->hook();

// 6c) Run the application!
$application->run();
