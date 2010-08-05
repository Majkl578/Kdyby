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
final class ExtendableRouter extends GradualRoute implements \ArrayAccess, \Countable, \IteratorAggregate
{

	/** @var string */
	const DADDY = 'daddy';

	/** @var array */
	protected $routes = array();

	/** @var array */
	protected $cachedRoutes;

	/** @var int */
	protected $routesDefaultCount = 0;


	/** @var array */
	protected static $node = Null;

	/** @var string */
	protected $name;

	/** @var string */
	protected $parent;



	/**
	 * @param string $name
	 * @param string $mask
	 * @param array $metadata
	 * @param int $flags
	 * @param SeoRouter $parent
	 */
	public function __construct($name = self::DADDY, $mask = Null, array $metadata = array(), $flags = 0, ExtendableRouter $parent = Null)
	{
		if( !String::match($name, "#^[a-zA-Z0-9]+$#") ){
			throw new \InvalidArgumentException("Route \$name must be alphanumeric string!");
		}

		if( $parent === Null AND $name !== self::DADDY ){
			throw new \InvalidArgumentException("You cannot name root route!");
		}

		$this->name = $name;
		$this->parent = $parent;
		$this->flags = $flags;

		if( $parent === Null ){ // children, daddy will now lookup for some cache, ups i mean cake!
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
	public function extend($name, $mask, array $metadata = array())
	{
		if( $name === self::DADDY ){
			throw new \InvalidArgumentException("Name '".self::DADDY."' is reserved!");
		}

		//$mask = $this->getMask() . '/' . ltrim($mask, '/');

		//$metadata = $this->getDefaults() + $metadata;

		if( isset($this[$name]) ){
			$have = 0;
			foreach( $this AS $rname => $route ){
				if( String::match($rname, "~^". preg_quote($name)."~i") ){
					$have += 1;
				}
			}
			$name .= ':'.(++$have);
		}

		$this[$name] = new self($name, $mask, $metadata, $this->flags, $this);
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
	protected function getCache()
	{
		return Nette\Environment::getCache("Router");
	}


	protected function getGradual(Nette\Web\IHttpRequest $httpRequest)
	{
		if( count(self::$node['routeLeafs']) == 0 ){
			return Null;
		}

		$leaf = array_shift(self::$node['routeLeafs']);

		if( isset($this[$leaf]) ){
			return $this[$leaf]->match($httpRequest);
		}

		return Null;
	}


	/**
	 * @param Nette\Web\IHttpRequest $httpRequest
	 * @return PresenterRequest
	 */
	public function match(Nette\Web\IHttpRequest $httpRequest)
	{
		$childrenParams = array();

		if( count($this) > 0 ){ // this is where daddy orders children to go on backyard have some fun!
			$gradualRequest = Null;

			if( self::$node !== Null AND count(self::$node['routeLeafs'])>0 ){ // children's play
				$gradualRequest = $this->getGradual($httpRequest);

			} elseif( $this->parent !== Null AND $this->parent->getName() === self::DADDY ) {
				foreach ($this->routes as $route) {
					if( $gradualRequest = $route->match($httpRequest) ){
					    break;
					}
				}
			}

			if( $gradualRequest !== Null ){
			        $childrenParams = $gradualRequest->getParams();

			}
		}

		// use actual route
		if( $this->parent !== Null ){ // children's play
			$request = parent::match($httpRequest);

			if( $request === NULL ){ // childrens are bored
				return NULL;
			}

			if( self::$node === Null ){ // seems like daddy's children are having some work to do
				$node = \Nette\Environment::getApplication()->getPresenterLoader()->getNode($appRequest);

				if( empty($node) ){ // damn! Try next daddy's children, maybe it knows...
					return NULL;
				}

				// nice! we have names of every children of children, ... ready to go!
				$node['routeLeafs'] = explode('/', $node['route']);
				self::$node = $node;

				// start parsing params from children
				$childrenParams += $this->getGradual($httpRequest);
			}

			// we have the load! let's send it to daddy
			return $childrenParams + $request->getParams();

		} else { // daddy is sending mommy a letter
			return new PresenterRequest(
				$appRequest->getPresenterName(), // FUCK TYPES!
				$httpRequest->getMethod(),
				$childrenParams,
				$httpRequest->getPost(),
				$httpRequest->getFiles(),
				array('secured' => $httpRequest->isSecured())
			);

		}

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



	/********************* interfaces ArrayAccess, Countable & IteratorAggregate *********************/



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

		if( $index === self::DADDY ){
			throw new \InvalidArgumentException("Name '".self::DADDY."' is reserved!");
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
