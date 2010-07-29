<?php

namespace Kdyby;

/**
 * Description of KdybyLoader
 *
 * @author hosiplan
 */
class KdybyLoader extends \Nette\Loaders\AutoLoader
{
	/** @var NetteLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'Kdyby\Application\ExtendableRouter' => '/Application/Routers/ExtendableRouter.php',
		'Kdyby\Application\Kdyby' => '/Application/Kdyby.php',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(KDYBY_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}
