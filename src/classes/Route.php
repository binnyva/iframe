<?php
namespace iframe;

class Route {
	private $actions = array();

	function post($route, $handler, $public = false) {
		$this->actions[$route] = array(
			'method'	=> 'POST',
			'route'		=> $route,
			'handler'	=> $handler,
			'public'	=> $public,
		);
	}

	function delete($route, $handler, $public = false) {
		$this->actions[$route] = array(
			'method'	=> 'DELETE',
			'route'		=> $route,
			'handler'	=> $handler,
			'public'	=> $public,
		);
	}

	function patch($route, $handler, $public = false) {
		$this->actions[$route] = array(
			'method'	=> 'PATCH',
			'route'		=> $route,
			'handler'	=> $handler,
			'public'	=> $public,
		);
	}

	/** Handle a get request.
	 * Example: $api->get('/user/{user_id}/fetch/{content_name}', function($user_id, $content_name) { dump($user_id, $content_name); });
	 */
	function get($route, $handler, $public = false) {
		$this->actions[$route] = array(
			'method'	=> 'GET',
			'route'		=> $route,
			'handler'	=> $handler,
			'public'	=> $public,
		);
	}

	/** Handle a request.
	 * Example: $api->request('/user/login', function() { print "Yo!"; });
	 */
	function request($route, $handler, $public = false) {
		$this->actions[$route] = array(
			'method'	=> '',
			'route'		=> $route,
			'handler'	=> $handler,
			'public'	=> $public,
		);
	}

	function notFound($handler) {
		$this->actions['404'] = array(
			'method'	=> '',
			'route'		=> '404',
			'handler'	=> $handler,
			'public'	=> false,
		);
	}

	function handle() {
		$method = $_SERVER['REQUEST_METHOD'];
		$route_found = false;

		$route_found = false;

		foreach ($this->actions as $route => $act) {
			if($act['method'] and $act['method'] != $method) continue;
			$vars = $this->_match_route($act['route']);
			if($vars === false) continue;
			else {
				call_user_func_array($act['handler'], $vars);
				$route_found = true;
			}
		}

		if(!$route_found) { // 404
			$handler = $this->actions['404']['handler'];
			call_user_func_array($handler, array());
		}
	}


	/**
	 * This will parse the Route given for the action with the actual route. If it matches, it will return a array with variables in the URL and its values
	 * For eg: If the action route is '/user/{user_id}/fetch/{content_name}' and the actual url is '/user/433/fetch/age', the returned array will be {'user_id': 433, 'content_name': 'age'}
	 */
	function _match_route($action_route, $route = '') {
		if(!$route) $route = $GLOBALS['QUERY']['_url'];
		$url_variables = array();

		if(strpos($route, '?') !== false) $route = @reset(explode("?", $route));// If URL has parameters, ignore them

		if(preg_match_all('#\{(\w+)\}#', $action_route, $matches)) {
			$vars = array();

			// First we convert the action route with its {variable_name} format to a preg-able string...
			$parsable_route = $action_route;
			for($i=0; $i<count($matches[0]); $i++) {
				$str = $matches[0][$i];
				$vars[] = $matches[1][$i]; // Get the list of variables in the route into a different array.
				$parsable_route = str_replace($str, "([^\\/]+)", $parsable_route);
			}

			// Then we see if the regexp matches the current route.
			$is_match = preg_match_all("#$parsable_route#", $route, $route_matches);
			if(!$is_match) return false; // No watch - get out.

			// Match - assign the values to the assoc array for return.
			$url_variables = array();
			for($i=0; $i<count($vars); $i++) {
				$url_variables[$vars[$i]] = $route_matches[$i+1][0];
			}
		} elseif ($action_route == $route) {
			return array();
		}

		if(!count($url_variables)) return false;
		return $url_variables;
	}
}

 
