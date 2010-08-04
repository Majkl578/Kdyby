<?php


namespace Kdyby;

use Nette\Loaders\LimitedScope;


/**
 * Description of KdybyLoader
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
final class KdybyLoader extends \Nette\Loaders\AutoLoader
{
	/** @var KdybyLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'kdyby\addonloader' => "/Loaders/AddonLoader.php",
		'kdyby\adminpresenter' => '/Presenters/AdminPresenter.php',
		'kdyby\application\extendablerouter' => '/Application/Routers/ExtendableRouter.php',
		'kdyby\application\seorouter' => '/Application/Routers/SeoRouter.php',
		'kdyby\application\kdyby' => '/Application/Kdyby.php',
		'kdyby\application\presenterloader' => '/Application/PresenterLoader.php',
		'kdyby\basepresenter' => '/Presenters/BasePresenter.php',
		'kdyby\basemodel' => '/Models/BaseModel.php',
		'kdyby\dependencies' => '/Dependencies.php',
		'kdyby\hooks' => '/Hooks.php',
		'kdyby\iaddonloader' => "/Loaders/IAddonLoader.php",
		'kdyby\imodificationloader' => "/Loaders/IModificationLoader.php",
		'kdyby\modificationloader' => "/Loaders/ModificationLoader.php",
	);

	/** @var array */
	public $addons = array();

	/** @var array */
	private $addonsClasses = array();

	/** @var int */
	private $addonsCount = 0;


	/** @var array */
	public $modifications = array();

	/** @var array */
	private $modificationsClasses = array();

	/** @var int */
	private $modificationsCount = 0;



	public function loadCache()
	{
		$c = $this->getCache();

		if( isset($c['addons']) ){
			$this->addonsClasses = $c['addonsClasses'];
		}

		if( isset($c['modifications']) ){
			$this->modificationsClasses = $c['modificationsClasses'];
		}
	}


	/**
	 * @return Nette\Caching\Cache
	 */
	private function getCache()
	{
		return \Nette\Environment::getCache("KdybyLoader");
	}


	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return KdybyLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}


	public function verifyLoader($class)
	{
		return (false);
	}


	public function loadAddon($addon)
	{
		if( !isset($this->addons[$addon->key]) ){
			list($file, $class) = $this->formatAddonLoader($addon->name, $addon->key);
			dump(get_defined_vars());
		}


	}


	public function loadModification($modification)
	{
		if( !isset($this->modifications[$addon->key]) ){
			list($file, $class) = $this->formatModificationLoader($addon->name, $addon->key);
			dump(get_defined_vars());
		}


	}


	public function formatAddonLoader($name, $key)
	{
		return array(
			'file' => APP_DIR . '/addons/' . String::lower($addon) . '-' . $key . '/loader.php',
			'class' => '\\Kdyby\\Addons\\' . $key . '\\Loader'
		);
	}


	public function formatModificationLoader($name, $key)
	{
		return array(
			'file' => APP_DIR . '/modifications/' . String::lower($addon) . '-' . $key . '/loader.php',
			'class' => '\\Kdyby\\Modifications\\' . $key . '\\Loader'
		);
	}


	/**
	 * Appends providen classes to addons list
	 * @param array $addons
	 */
	public function registerAddonClasses(array $classes)
	{
		$this->addonsClasses += $classes;

		if( count($this->addonsClasses) != $this->addonsCount ){
			$this->getCache()->save('addonClasses', $this->addonsClasses);

			$this->addonsCount = count($this->addonsClasses);
		}
	}


	/**
	 * Appends providen classes to modifications list
	 * @param array $modifications 
	 */
	public function registerModificationClasses(array $classes)
	{
		$this->modificationsClasses += $classes;

		if( count($this->modificationsClasses) != $this->modificationsCount ){
			$this->getCache()->save('modificationClasses', $this->modificationsClasses);

			$this->modificationsCount = count($this->modificationsClasses);
		}
	}


	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');

		if( isset($this->list[$type]) ){
			LimitedScope::load(KDYBY_DIR . $this->list[$type]);
			self::$count++;

		} elseif( isset($this->addonsClasses[$type]) ){
			LimitedScope::load(APP_DIR . $this->addonsClasses[$type]);
			self::$count++;

		} elseif( isset($this->modificationsClasses[$type]) ){
			LimitedScope::load(KDYBY_DIR . $this->modificationsClasses[$type]);
			self::$count++;
		}
	}

}
