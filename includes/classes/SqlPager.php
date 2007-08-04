<?php
/**
 * :TODO: Currently, the system does not handle arrays in the URL well.
 * :TODO: Use Database abstraction
 */
class SqlPager {
	///Public Variables
	var $query;
	var $items_per_page;
	var $page;
	var $total_items;
	var $total_pages;
	var $current_url;
	
	///The current mode of the system. Possible values are 'd' = Development Mode, 't' = Testing Mode, 'p' = Production Mode
	var $mode = 'd';
	var $opt = array(
		'links_count'	=> 10, //If 0, shows all links
	);

	///The text used in the pager is customizable. HTML is allowed.
	var $text = array(
		'previous' => '&lt;' , 'next' => '&gt;', 		//Next and Previous Texts
		'first' => '&lt;&lt;', 'last' => '&gt;&gt;', 	//First and Last Links
		'current_page_indicator' => array(
			'left'	=> '[',
			'right'	=> ']'
		),
		'link_seperator' => " "							//The text between the paging links(Page numbers). 
	);

	///Private Variables
	var $_sql_resource;
	var $_pager_query;

	/**
	 * Constructor
	 * Argument : $query - The query that should be used for paging
	 * 			  $items_per_page - The default number of items per page [OPTIONAL]
	 */
	function SqlPager($query, $items_per_page = 10) {
		$query = preg_replace('/ LIMIT .+/i','',$query);//Remove the 'limitation' if there is one
		$this->query = $query;

		//Before anything, get all the necessary data.
		//Get the value of $items_per_page from the query or from the parameter
		$this->items_per_page = ($this->_getParam('sp_items_per_page')) ? 
					$this->_getParam('sp_items_per_page') : $items_per_page;

		//Get the current page number
		$this->page = ($this->_getParam('sp_page')) ? $this->_getParam('sp_page') : 1;

		$this->current_url = $_SERVER["REQUEST_URI"]; //What about CGI Handlers?

		$offset = ($this->page - 1) * $this->items_per_page;
		$this->_pager_query = $query . " LIMIT $offset," . $this->items_per_page;
	}
	
	/**
	 * Returns the SQL resource of the pager.
	 * Return : The SQL resource of the pager.
	 */
	function getSql() {
		$this->_sql_resource = mysql_query($this->_pager_query);
		if(!$this->_sql_resource) {
			$this->_error("Query error: ".mysql_error()."!<br />" . $this->_pager_query);
			return false;
		}
		$total_items_sql = mysql_query($this->query);
		$this->total_items = mysql_num_rows($total_items_sql);
		$this->total_pages = ceil($this->total_items / $this->items_per_page);

		return $this->_sql_resource;
	}
	
	/**
	 * Returns all the items for one page in an array.
	 * Returns : All the items for one page in a list.
	 */
	function getPage() {
		$resource = $this->getSql();
		$result = array();
		while($row = mysql_fetch_assoc($resource)) {
			$result[] = $row;
		}
		return $result;
	}

	//////////////////////////////////// Functions that print
	/**
	 * Prints the pager.
	 */
	function showPager() {
		if($this->total_pages == 1) return; //Just 1 page - don't need pager.
		
		$from = 1;
		$to = $this->total_pages;

		// Decides many page numbers should be shown
		if($this->opt['links_count']) {
			$difference = intVal($this->opt['links_count'] / 2);
			$from = $this->page - $difference + 1;
			$to = $this->page + $difference;

			//Make sure the numbers are in range.
			if($from < 1) {
				//The numbers that cannot be put in the right side can be put in the left side
				$to = $to + (-$from);//$from is negetive
				$from = 1;
			}
			if($to > $this->total_pages) {
				$from = $from - ($to - $this->total_pages);
				$to = $this->total_pages;
				if($from < 1) $from = 1;
			}
		}

		for($i = $from; $i <= $to; $i++) {
			if($i == $this->page) { //Current Page
				print $this->text['current_page_indicator']['left'] . $i . $this->text['current_page_indicator']['right'];
			} else {
				print '<a class="sp-page-number" href="' . $this->getLinkToPage($i) . '">'.$i."</a>";
			}
			print $this->text['link_seperator'];
		}
	}
	
	/**
	 * Returns the link to the page of the given page number
	 * Argument : $page - The page number of the page of which's link you want. Example : 2
	 * Return	: The url of the page - index.php?sp_page=2
	 */
	function getLinkToPage($page) {
		if($page > $this->total_pages) return '';
		
		return $this->_getLinkParameters($this->current_url,array('sp_page'=>$page));
	}


	//////////////////////////////////////// Private Functions ///////////////////////////////////////
	/**
	 * Returns the User input from $_REQUEST after escaping it using mysql_real_escape_string()
	 * Argument : $string - Parameter name
	 * Return : Parameter value, escaped.
	 */
	function _getParam($string) {
		if(!isset($_REQUEST[$string])) return '';
		if(is_array($_REQUEST[$string])) return $_REQUEST[$string];
		return mysql_real_escape_string($_REQUEST[$string]);
	}
	
	/**
	 * Adds the given parameters to the given URL are retuns the result. The returned URL will be XHTML complient
	 * Argument : $url - The URL of the page.
	 *			  $params(Array) - An associative array holding the parameters that should be added to the URL. 
	 * Example : <code>_getLinkParameters('index.php?user=1',array('sp_page'=>7,'sp_items_per_page'=>5))</code>
	 */
	function _getLinkParameters($url , $params) {
		if(!$params) return $url;
		
		$all_parameters = array();
		$url_parameters = array();
		$link = $url;
		
		//Break down the URL - and build it up again
		if(strpos($link,'?') === false) { //No '?' in the url
			$file = $link;
		} else {
			$parts = explode('?',$url);
			$file = $parts[0];
			$all_parameters = preg_split('/\&(amp;)?/',$parts[1]);
			foreach($all_parameters as $param) {
				$bits = explode('=',$param);
				$url_parameters[$bits[0]] = $bits[1];
			}
		}

		// Add the url's parameters to the parameters supplied in the second argument. Mix well.
		$params = $params + $url_parameters;

		$params_arr = array();
		foreach($params as $key=>$value) {
			//Some stuff must be removed - iFrame specific
			if($key == 'success' or $key == 'error') continue;

			if(gettype($value) == 'array') { //Handle array data properly - :TODO: there will be problems
				foreach($value as $val) {
					if($val) $params_arr[] = $key . '[]=' . urlencode($val);
				}
			} else {
				if($value) $params_arr[] = $key . '=' . urlencode($value);
			}
		}
		
		$link = $file . '?' . implode('&amp;',$params_arr);

		return $link;
	}

	/**
	 * Shows an error message based on mode.
	 */
	function _error($message) {
		if($this->mode == 'd') die($message);
		elseif($this->mode == 't') print($message);
	}
}