<?php
class Route {
    
    private $base_path = '';
	private $request_uri = '';
	private $routes_array = array();
	
	/*
	 *	Returns base path of current url
	 *	Eg for http://some-domain.com/folder-1/folder-2/index.php will give /folder-1/folder-2
	 */
	protected function get_base_path() {
		return $this->base_path = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
	}
    
    /*
	 * The following function will strip the script name from URL i.e.  http://www.something.com/search/book/fitzgerald will become /search/book/fitzgerald
	 */
	protected function get_request_uri() {
		$uri = substr($_SERVER['REQUEST_URI'], strlen($this->get_base_path()));
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
		return $this->request_uri = '/' . trim($uri, '/');
		//return $this->request_uri = trim($uri, '/');
	}
	
	/*
	 * returns request_uri in array
	 * http://www.something.com/search/book/java will return array(0=>search,1=>book,2=>java)
	 */
	protected function get_request_uri_array() {
		$base_url = $this->get_request_uri();
		$request_uri_array = array();
		$array = explode('/', $base_url);
		foreach( $array as $route ) {
			if(trim($route) != '')
				array_push($request_uri_array, $route);
		}
		return $request_uri_array;
	}
    
	/*
	 * @param array eg:
					array('GET','tasks/add','Controller@method')
					array('GET','tasks/edit/{id}','Controller@method') //id will be passed as first param of method,
																		method may have more than one param, ofcourse they should be option with deault value as they wont be used by Route
					array('POST','task/add',function(){ echo "Served directly from routes"; })
	 */			
    public function add($array=array()) {
        $this->routes_array[] = $array;
    }
	
	public function dispatch() {
		$request_uri_array = $this->get_request_uri_array();
		foreach( $this->routes_array as $route_array ) {
			
			$current_request_uri = $this->get_request_uri();	
			
			/* Check if HTTP verb matches current request */
			if( $route_array[0] != 'ANY' && $_SERVER['REQUEST_METHOD'] != $route_array[0] )
			continue;
			
			/* This will contain the route parameters to be captures and sent back as controller method arguments */
			$variables = array();
			
			$route_array_as_array = array_filter(explode('/',$route_array[1]));
						
			if( count($route_array_as_array) != count($request_uri_array) )
			continue;
			
			$match_occured = true;
			for( $i=0; $i<count($route_array_as_array); $i++ ) {
				preg_match_all('/{(.*?)}/', $route_array_as_array[$i], $matches); //get all {} variables
				
				if( $route_array_as_array[$i] == $request_uri_array[$i] ) {
					
				}
				elseif( !empty($matches[0]) ) {
					$variables[] = $request_uri_array[$i];
				}
				else {
					$match_occured = false;
					continue;
				}
			}
			
			if(!$match_occured) continue;
						
			return $this->get_route_callback($route_array,$variables);
			
		}
		return [];
	}
	
	public function get_route_callback($route_array,$variables) {
		$return_route_match_array = array();
		if( is_callable($route_array[2]) ) { //check if third param is function
			$return_route_match_array['is_closure'] = true;
			$return_route_match_array['closure'] = $route_array[2];
		}
		else {
			$return_route_match_array['is_closure'] = false;
			$controller_method = explode('@',$route_array[2]);
			$return_route_match_array['controller'] = $controller_method[0];
			$return_route_match_array['method'] = $controller_method[1];
			$return_route_match_array['params'] = $variables;
		}
		return $return_route_match_array;
	}
    
}