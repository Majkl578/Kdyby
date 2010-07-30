<?php


namespace Kdyby;




/**
 * Description of ModificationLoader
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class ModificationLoader extends AddonLoader implements IModificationLoader
{

	static $modificationLoaders = array();

	protected $modificationClasses = array();

	

	public function getModification($modification)
	{
		return self::$modificationLoaders[$modification];
	}

}
