<?php


namespace Kdyby;




/**
 * Description of Dependencies
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class Dependencies extends \Nette\Object
{

	/**
	 * Package version
	 */
	const VERSION = 10000; // 1.0.0

	/**
	 * Unique key generated by system for specific extension
	 */
	const DEP_KEY = NULL;

	/**
	 * List of dependencies that must be met for appropriate behaviour
	 */
	protected $dependencies = array(
		//'extensionName:dependencyKey' => array('>=', 'version');
	);



	final public function getMissingDependencies()
	{
		$missing = array();

		foreach( (array)$this->dependencies AS $extension => $version ){
			if( !$this->isDependencyMet($depKey, $version) ){
				$missing[$depKey] = $version;
			}
		}

		return $missing;
	}


	final public function isDependencyMet($depKey, $version)
	{
		
	}

}