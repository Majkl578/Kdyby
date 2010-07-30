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
		'kdyby\application\extendablerouter' => '/Application/Routers/ExtendableRouter.php',
		'kdyby\application\kdyby' => '/Application/Kdyby.php',
		'kdyby\application\presenterloader' => '/Application/PresenterLoader.php',
		'kdyby\basepresenter' => '/Presenters/BasePresenter.php',
		'kdyby\hooks' => '/Hooks.php',
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
			$this->addonsClasses = $c['addons'];
		}

		if( isset($c['modifications']) ){
			$this->modificationsClasses = $c['modifications'];
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


	public function loadAddon($addon)
	{
		
	}


	public function loadModification($modification)
	{
		
	}


	/**
	 * Appends providen classes to addons list
	 * @param array $addons
	 */
	public function registerAddonClasses(array $classes)
	{
		$this->addonClasses += $classes;

		if( count($this->addonClasses) != $this->addonsCount ){
			$this->getCache()->save('addons', $this->addonClasses);

			$this->addonsCount = count($this->addons);
		}
	}


	/**
	 * Appends providen classes to modifications list
	 * @param array $modifications 
	 */
	public function registerModificationClasses(array $classes)
	{
		$this->modificationClasses += $classes;

		if( count($this->modificationClasses) != $this->modificationsCount ){
			$this->getCache()->save('modifications', $this->modificationClasses);

			$this->modificationsCount = count($this->modifications);
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

		} elseif( isset($this->addons[$type]) ){
			LimitedScope::load(APP_DIR . $this->addonsClasses[$type]);
			self::$count++;

		} elseif( isset($this->modifications[$type]) ){
			LimitedScope::load(KDYBY_DIR . $this->modificationsClasses[$type]);
			self::$count++;
		}
	}

}
