<?php


namespace Kdyby;




/**
 * Description of AddonLoader
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class AddonLoader extends Dependencies implements IAddonLoader
{

	static $addonLoaders = array();
	
	protected $addonClasses = array();



	public function getAddon($addon)
	{
		return self::$addonLoaders[$addon];
	}

}
