<?php


namespace Kdyby\Application;

use Nette,
	Nette\Application\PresenterRequest;



/**
 * Description of PresenterLoader
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class PresenterLoader extends \Nette\Object implements \Nette\Application\IPresenterLoader
{
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var string */
	private $baseDir;

	/** @var Kdyby\KdybyLoader */
	private $loader;

	/** @var array */
	private $cache = array();

	/** @var \DibiRow */
	static $node = Null;


	/**
	 * @param \Kdyby\KdybyLoader $loader
	 * @param string $baseDir
	 */
	public function __construct(\Kdyby\KdybyLoader $loader, $baseDir)
	{
		$this->loader = $loader;
		$this->baseDir = $baseDir;
	}


	/**
	 *
	 * @param string $params
	 * @return \DibiRow
	 */
	public function getNode($params = Null)
	{
		return array(
			'presenter' => "f1r5tk3y3v3r:Page",
			'route' => 'node~blah~tadyda',
			'allowedActions' => "",
			'defaultAction' => "",
			'layout' => "",
			'template' => "",
		    );

		if( self::$node == Null OR $params !== Null ){
			self::$node = dibi::fetch(
				'SELECT * FROM %n', $this->table,
				'WHERE %n = %s', 'id', $params['id']
			    );
		}

		return self::$node;
	}



	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !Nette\String::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				Nette\Loaders\LimitedScope::load($file);
			}

			if (!class_exists($class)) {
				throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found in '$file'.");
			}
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}

}
