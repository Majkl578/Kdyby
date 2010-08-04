<?php


namespace Kdyby\Application;

use Nette,
	Nette\String,
	Nette\Application\PresenterRequest;



/**
 * Description of ExtendableRouter
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
final class ExtendableRouter extends \Nette\Application\Route implements \ArrayAccess, \Countable, \IteratorAggregate
{

	/** @var array */
	private $routes = array();

	/** @var array */
	private $cachedRoutes;

	/** @var int */
	private $routesDefaultCount = 0;
	
	
	/** @var int */
	private $flags = 0;


	/** @var array */
	protected static $node = Null;

	/** @var string */
	private $name;

	/** @var string */
	private $parent;



	/**
	 * @param string $name
	 * @param string $mask
	 * @param array $metadata
	 * @param int $flags
	 * @param SeoRouter $parent
	 */
	public function __construct($name = 'daddy', $mask = Null, array $metadata = array(), $flags = 0, ExtendableRouter $parent = Null)
	{
		if( !String::match($name, "#^[a-zA-Z0-9]+$#") ){
			throw new \InvalidArgumentException("Route \$name must be alphanumeric string!");
		}

		if( $parent === Null AND $name !== 'daddy' ){
			throw new \InvalidArgumentException("You cannot name root route!");
		}

		$this->name = $name;
		$this->parent = $parent;
		$this->flags = $flags;

		if( $parent === Null ){ // children, daddy will now lookup for some cake, ups i mean cache!
			$c = $this->getCache();

			if( isset($c['routes']) ){
				$this->routes = $c['routes'];
				$this->routesDefaultCount = count($this);
			}

		} else {
			parent::__construct($mask, $metadata, $flags);
		}
	}


	/**
	 * Creates new route with parentmask + given mask
	 * @param string $name
	 * @param string $mask
	 * @param array $metadata
	 * @param int $flags
	 */
	public function extend($name, $mask, array $metadata = array(), $flags = 0)
	{
		$mask = $this->getMask() . '/' . ltrim($mask, '/');

		$metadata = $this->getDefaults() + $metadata;

		$flags = $this->flags + $flags;

		if( isset($this[$name]) ){
			$have = 0;
			foreach( $this AS $rname => $route ){
				if( String::match($rname, "~^". preg_quote($name)."~i") ){
					$have += 1;
				}
			}
			$name .= ':'.(++$have);
		}

		$this[$name] = new self($name, $mask, $metadata, $flags, $this);
	}


	public function getName()
	{
		return $this->name;
	}


	/**
	 * Invalidates routes
	 */
	public function invalidateRoutes()
	{
		if( count($this) != $this->routesDefaultCount ){
			$this->getCache()->save('routes', $this->routes);

			$this->routesDefaultCount = count($this);
		}
	}


	/**
	 * @return Nette\Caching\Cache
	 */
	private function getCache()
	{
		return Nette\Environment::getCache("Router");
	}


	/**
	 * @param Nette\Web\IHttpRequest $httpRequest
	 * @return PresenterRequest
	 */
	public function match(Nette\Web\IHttpRequest $httpRequest)
	{
		if( count($this) > 0 ){
			if( self::$node !== Null ){
				return $route[array_shift(self::$node['route'])]->match($httpRequest);
			}

			foreach ($this->routes as $route) {
				$appRequest = $route->match($httpRequest);
				if ($appRequest !== NULL) {
					return $appRequest;
				}
			}
		}

		// use actual route

		$request = parent::match($httpRequest);

		if( $request === NULL ){ 
			return NULL;
		}

		if( self::$node === Null ){
			$node = \Nette\Environment::getApplication()->getPresenterLoader()->getNode($request);

			if( empty($node) ){
				return NULL;
			}

			$node['route'] = explode('/', $node['route']);
			self::$node = $node;

			return $route[array_shift(self::$node['route'])]->match($httpRequest);
		}


		return new PresenterRequest(
			$node, // FUCK TYPES!
			$httpRequest->getMethod(),
			$params,
			$httpRequest->getPost(),
			$httpRequest->getFiles(),
			array('secured' => $httpRequest->isSecured())
		);

	}


	/**
	 * @param PresenterRequest $appRequest
	 * @param Nette\Web\IHttpRequest $httpRequest
	 * @return string
	 */
	public function constructUrl(PresenterRequest $appRequest, Nette\Web\IHttpRequest $httpRequest)
	{
		if( count($this) > 0 ){
		    if ($this->cachedRoutes === NULL) {
			    $routes = array();
			    $routes['*'] = array();

			    foreach ($this->routes as $route) {
				    $presenter = $route instanceof Route ? $route->getTargetPresenter() : NULL;

				    if ($presenter === FALSE) continue;

				    if (is_string($presenter)) {
					    $presenter = strtolower($presenter);
					    if (!isset($routes[$presenter])) {
						    $routes[$presenter] = $routes['*'];
					    }
					    $routes[$presenter][] = $route;

				    } else {
					    foreach ($routes as $id => $foo) {
						    $routes[$id][] = $route;
					    }
				    }
			    }

			    $this->cachedRoutes = $routes;
		    }

		    $presenter = strtolower($appRequest->getPresenterName());
		    if (!isset($this->cachedRoutes[$presenter])) $presenter = '*';

		    foreach ($this->cachedRoutes[$presenter] as $route) {
			    $uri = $route->constructUrl($appRequest, $httpRequest);
			    if ($uri !== NULL) {
				    return $uri;
			    }
		    }
		}

		if( $this->parent === Null ){
			return Null;
		}

		// use actual route

		$actualParams = $httpRequest->getParams();

		$data = dibi::fetch(
			    'SELECT [uri],[lang] FROM %n', $this->table,
			    'WHERE %n = %s', 'id', $actualParams['id']
		    );
		if (empty($data)) return NULL;

		$uri = $context->getUri()->basePath.$data['lang'].'/'.$actualParams['id'].'-'.$data['uri'];
		//unset($actualParams['lang'], $actualParams['id'],$actualParams['uri']);
		//$query = http_build_query($actualParams, '', '&');
		//if ($query !== '') $uri .= '?' . $query;

		return $uri;
	}



	/********************* interfaces ArrayAccess, Countable & IteratorAggregate ****************d*g**/



	/**
	 * Adds the router.
	 * @param  mixed
	 * @param  IRouter
	 * @return void
	 */
	public function offsetSet($index, $route)
	{
		if (!($route instanceof Nette\Application\IRouter)) {
			throw new \InvalidArgumentException("Argument must be IRouter descendant.");
		}
		$this->routes[$index] = $route;
	}



	/**
	 * Returns router specified by index. Throws exception if router doesn't exist.
	 * @param  mixed
	 * @return IRouter
	 */
	public function offsetGet($index)
	{
		return $this->routes[$index];
	}



	/**
	 * Does router specified by index exists?
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->routes[$index]);
	}



	/**
	 * Removes router.
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($index)
	{
		unset($this->routes[$index]);
	}



	/**
	 * Iterates over routers.
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return $this->routes;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->routes);
	}
}
