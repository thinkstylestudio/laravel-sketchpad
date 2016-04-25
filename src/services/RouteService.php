<?php namespace davestewart\doodle\services;

use App;
use davestewart\doodle\objects\DoodleConfig;
use davestewart\doodle\objects\route\ControllerReference;
use davestewart\doodle\objects\route\PathReference;
use davestewart\doodle\traits\GetterTrait;
use ReflectionMethod;
use Route;

/**
 * RouteService
 *
 * Responsible for:
 *
 * - determining the possible routes => Controllers from the doodles/ folder downwards
 * - matching any called routes to said controllers
 * - creating any wildcard routes if required
 *
 * @package davestewart\doodle\objects\file
 */
class RouteService extends DoodleConfig
{
	
	use GetterTrait;

	// -----------------------------------------------------------------------------------------------------------------
	// PROPERTIES

		/**
		 * The root controller namespace for doodle/ controllers
		 *
		 * @var
		 */
		public $namespace;

		/**
		 * An array of 'route' => RouteReference instances, representing all found
		 * folders / controllers from the doodles/ controller folder down
		 *
		 * @var PathReference[]
		 */
		public $routes;


	// -----------------------------------------------------------------------------------------------------------------
	// INSTANTIATION

		/**
		 * RouteService constructor.
		 *
		 * Sets up the RouteService with the values it needs determine or match routes
		 *
		 * Parameters are all from the Doodle config file
		 *
		 * @param   string   $route         the base r
		 * @param   string   $path
		 * @param   string   $namespace
		 */
		public function __construct($route, $path, $namespace)
		{
			// parameters
			$this->route        = $route;
			$this->path         = $path;
			$this->namespace    = $namespace;

			// properties
			$this->routes       = [];

			// process
			$this->process();
			ksort($this->routes);
		}


	// -----------------------------------------------------------------------------------------------------------------
	// METHODS

		/**
		 * Sets the Laravel application routes for all the found controllers
		 *
		 * @return $this
		 */
		public function setRoutes()
		{
			foreach ($this->routes as $route => $ref)
			{
				if($ref->type === 'controller')
				{
					$this->wildcard($route, $ref->controller);
				}
			}
			return $this;
		}

		/**
		 * Reverse route lookup
		 *
		 * Compares a given route against routes determined when the controllers folder was processed
		 *
		 * Determines the controller, method and parameters to call if there is a match
		 *
		 * @param   $route
		 * @return  PathReference|ControllerReference|null
		 */
		public function getRoute($route)
		{
			// variables
			$route   = $this->folder($route);

			// debug
			//pr($path, $this->routes);

			// first, attempt to find an exact match
			// an exact match will either be a controller or a folder
			if(isset($this->routes[$route]))
			{
				return $this->routes[$route];
			}

			// otherwise, the passed path will be at least a "controller/method" and possibly
			// a "controller/method/with/some/parameters" in which case, we need to find
			// the nearest partial match, then extract the component parts
			else
			{
				// variables
				$ref    = null;
				$match  = '';

				// loop over routes and grab matches
				// the last full match will be the one that we want
				foreach($this->routes as $key => $value)
				{
					if(strpos($route, $key) === 0)
					{
						$match      = $key;
						$ref        = $value;
					}
				}

				// if we got a matching route, update the ref with method and params
				if($ref)
				{
					// variables
					$methodUri      = trim(substr($route, strlen($match)), '/');
					$segments       = explode('/', $methodUri);

					// properties
					$ref->method    = array_shift($segments);
					$ref->params    = $segments;
					return $ref;
				}
			}

			// return
			return null;
		}


	// -----------------------------------------------------------------------------------------------------------------
	// PROCESSING METHODS

		/**
		 * Recursive path processing function
		 *
		 * Sets controllers and folders elements as they are found
		 *
		 * @param string $path  The base controller path-relative path to the folder
		 */
		protected function process($path = '')
		{
			// variables
			$root               = $this->folder($this->path . $path);
			$files              = array_diff(scandir($root), ['.', '..']);

			// folders
			$this->addFolder($path);

			// loop
			foreach ($files as $file)
			{
				// variables
				$filepath = $root . $file;

				// parse
				if(is_dir($filepath))
				{
					$this->process($path . $file . '/');
				}
				else if(is_file($filepath) && preg_match('/Controller.php$/', $filepath))
				{
					$this->addController($path, $file);
				}
			}
		}

		/**
		 * Adds a new RouteReference obejct
		 *
		 * @param string        $route
		 * @param PathReference $ref
		 */
		protected function addRoute($route, $ref)
		{
			$ref->route = $route;
			$this->routes[$route] = $ref;
		}

		/**
		 * Adds a folder to the internal routes array
		 *
		 * @param $path
		 */
		protected function addFolder($path)
		{
			$this->addRoute($this->route . $path, new PathReference($this->path . $path));
		}

		/**
		 * Adds a controllrt to the internal routes array
		 *
		 * @param $path
		 * @param $file
		 */
		protected function addController($path, $file)
		{
			// variables
			$class      = preg_replace('/.php$/', '', $file);
			$name       = preg_replace('/Controller$/', '', $class);
			$route      = strtolower($this->route . $path . $name . '/');
			$controller = $this->namespace . str_replace('/', '\\', $path) . $class;

			// set route
			$this->addRoute($route, new ControllerReference($this->path . $path . $file, $controller));
		}


	// ------------------------------------------------------------------------------------------------
	// ROUTING

		/**
		 * Wildcard routing method
		 *
		 * @param   string          $route          Base route, i.e. 'test'
		 * @param   string          $controller     Path to controller, i.e. 'TestController'
		 * @param   string|array    $verbs          Optional Route verb(s), i.e. 'get'; defaults to ['get', 'post']

		 * @usage   wildcard('test', 'TestController');
		 * @usage   wildcard('test', 'TestController', ['get']);
		 * @usage   wildcard('test', 'Test\TestController');
		 *
		 * @example	http://localhost/test/hello/world
		 *
		 * @author	Dave Stewart | dave@davestewart.io
		 */
		protected function wildcard($route, $controller, $verbs = ['get', 'post'])
		{
			// variables
			$stack      = Route::getGroupStack();
			$namespace  = end($stack)['namespace'];
			$controller = $namespace . '\\' . $controller;

			// routing
			Route::match($verbs, rtrim($route, '/') . '/{method}/{params?}', function($method, $params = null) use ($controller)
			{
				RouteService::call($controller, $method, $params);

			})->where('params', '.*');
		}

		/**
		 * Calls a controller and methods, resolving any dependency injection
		 *
		 * @param   string      $controller     The FQ name of the controller
		 * @param   string      $method         The name of the method
		 * @param   array|null  $params         An optional arr ay of parameters
		 * @return  mixed                       The result of the call
		 */
		public static function call($controller, $method, $params = null)
		{
			// method
			$callable = $controller . '@' . $method;

			// call
			if($params)
			{
				// variables
				$values     = is_array($params) ? $params : explode('/', $params);
				$ref        = new ReflectionMethod($controller, $method);
				$params     = $ref->getParameters();
				$args       = [];

				// map route segments to the method's parameters
				foreach ($params as /** @var \ReflectionParameter */ $param)
				{
					// parse signature [match, optional, type, name, default]
					preg_match('/<(required|optional)> (?:([\\\\a-z\d_]+) )?(?:\\$(\w+))(?: = (\S+))?/i', (string) $param, $matches);

					// assign untyped segments
					if($matches[2] == null)
					{
						$args[$matches[3]] = array_shift($values);
					}
				}

				// append any remaining values
				$values = array_merge($args, $values);

				// call
				return App::call($callable, $values);
			}
			else
			{
				return App::call($callable);
			}
		}
	

	// ------------------------------------------------------------------------------------------------
	// UTILITIES

		/**
		 * Utility function to return a folder path with a slash at the end
		 *
		 * @param $path
		 * @return string
		 */
		protected function folder($path)
		{
			return rtrim($path, '/') . '/';
		}


}
