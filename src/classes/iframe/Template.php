<?php
namespace iframe\iframe;
use iframe\App;

/**
 * File	: classes/Template.php
 * Class : Template
 * All Template action are in this class - Templating and Model, anyway
 */
class Template {
	public $page		= ''; ///This variable holds the location of the controller page.
	public $template	= ''; ///Holds the location of the view(or template) page. This value can be deduced from the controller page or can be explicitly set using the setTemplate() funciton.
	public $model		= '';
	public $model_name	= '';
	public $controller	= '';

	public $title		= ''; ///The title of the page - use setTemplate() to set it.
	public $options		= array(
		'layout_file'		=> 'templates/layout/page.php', ///This should contain the layout of the site.
		'template_folder'	=> 'templates',
		'model_folder'		=> 'models',
		'insert_layout'		=> true
	);
	public $includes	= array();
	public $css_includes= array();
	public $js_includes = array();
	public $css_folder	= 'assets/css/';
	public $js_folder	= 'assets/js/';
	
	public $content	= array(
		'head'		=>	''
	);

	/**
	 * Constructor
	 * Finds the location of the current file - eg. 'advertise/myaccount.php'
	 */
	function __construct() {
		$this->setPage(App::$config["PHP_SELF"]);
		$this->setController();
		$this->setModel();
	}
	
	/////////////////////////////// Setting functions ////////////////////////////////
	/**
	 * Takes a file name as the argument and find its template based on the file name.
	 * Argument : $file_name - The file name of the controller document.
	 */
	function setPage($file_name) {
		//Remove the absolute site path while conserving the directory structure in the URL
		$file_name = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_name);
		
		$escaped_path = preg_replace('/([\/\\\.\?\~\\=\_\-\,])/','\\\$1', App::$config['app_absolute_path']);
		$file_name = preg_replace('/' . $escaped_path . '/', '', $file_name, 1); //Replace just 1 time
		$file_name = preg_replace('/controllers\//', '', $file_name);//If we are following mvc architecture
		if(strpos($file_name,'/') === 0) $file_name = substr($file_name, 1);

		//Remove the query parts		
		$parts = explode('?', $file_name);
		$file_name = $parts[0];
		//Make sure that it is a file call and not a directory
		if(!strpos(basename($file_name), '.php')) { ///:TODO: This could be .php5 or something - could cause problems.
			$file_name .= 'index.php';
		}

		$this->page = $file_name;
	}
	
	/**
	 * Find the controller for the current page - usually the folder's name is the controller.
	 */
	function setController($controller = '') {
		$controller = dirname($this->page);
		if($controller == '.') $controller = '';
		$this->controller = $controller;
	}

	/**
	 * This will set the template for the page - you can specify the file to be used as the template.
	 * In most cases, this is an internal function. But it can be called by the user as well.
	 * Argument : $template_file - This is the template file to be used - it must be kept in the template folder.
	 *			  $use_exact_path - If this is false, MVC will try to find the template file using some rules(search in the templates folder etc. If true, it will just include the exact page.
	 */
	function setTemplate($template_file, $use_exact_path = false) {
		$this->findResources($template_file);

		if(!$use_exact_path) {
			$template_file = joinPath(App::$config['app_folder'], $this->options['template_folder'], $template_file);
			//Plugins are a special case.
			if(strpos(App::$config['PHP_SELF'],'plugins') !== false) {
				$template_file = 'template.php';
			}
		}

		if(!file_exists($template_file)) {
			//Search the template folder for that file
			if (file_exists( joinPath($this->options['template_folder'],$template_file) ))
				$this->template = joinPath($this->options['template_folder'],$template_file);
			else 
				$this->template = '';
		}
		else $this->template = $template_file;
	}
	
	/**
	 * See if a Model page exist for this page - if it is there, include it
	 */
	function setModel($model_file = false) {
		$this->model = '';
		if($model_file === '') { //The coder has specifically called setModel('') - so don't use a model
			return;
		}
		$model_file = $this->controller . '.php';
		$model_folder = joinPath(App::$config['app_folder'], $this->options['model_folder'], '/');
		$model_name = '';
	
		if(!file_exists($model_file)) {
			//Search the template folder for that file
			if (file_exists( joinPath($model_folder, $model_file))) {
				$this->model = $model_file;
			} else {
				//Try to find the model with newer methods.
				//First, try to make the first letter uppercase
				if (file_exists( joinPath($model_folder, ucfirst($model_file)) )) {
					$this->model = ucfirst($model_file);
					
				//If thats not there, look for all lowercase model name.
				} elseif(file_exists( joinPath($model_folder, strtolower($this->controller) . '.php') )) {
					$this->model = strtolower($this->controller) . '.php';
					return;
				}
			}
		}
		else $this->model = $model_file;
		$model_name = $this->model;

		if($this->model) {
			include_once(joinPath($model_folder, $this->model));
			$this->model_name = str_replace('.php','',$model_name);
		}
	}
	
	/**
	 * Finds all the CSS and JS files that must be included in this page.
	 */
	function findResources($template_file) {
		// Common CSS / JS files
		if(file_exists(joinPath(App::$config['app_folder'], $this->js_folder, "common.js"))) $this->addResource("common.js",  'js');
		if(file_exists(joinPath(App::$config['app_folder'], $this->css_folder, "common.css"))) $this->addResource("common.css",  'css');

		// Template specific css/js
		$css_file = preg_replace('/\.php$/','.css',$template_file);
		$js_file  = preg_replace('/\.php$/','.js', $template_file);

		if(file_exists(joinPath(App::$config['app_folder'], $this->css_folder, $css_file))) $this->addResource($css_file, 'css');
		if(file_exists(joinPath(App::$config['app_folder'], $this->js_folder, $js_file))) $this->addResource($js_file,  'js');
	}

	/**
	 * This will set the title of the page.
	 * Argument : $title - The string to be used inside the &lt;title>&lt;/title> tag.
	 */
	function setTitle($title) {
		if($title) $this->title = $title;
	}

	/**
	 * Add a resource file to the HTML page - like a css file or a javascript file.
	 * Arguments :	$file - The file to be included in the output HTML file
	 *				$type - The type of file - this says whether the file is a javascript or a css file. Must have the value('css' or 'js')
	 *				$use_exact_path(Boolean) - Uses the exact value given as the $file argument in the 'href' part. If this is false, the system will gusess a better value.
	 */
	function addResource($file, $type="", $use_exact_path=false) {
		if(!$file) return;

		if(preg_match('#https?\://#', $file)) $use_exact_path = true; // If a full absolute url is given, use exact path.
		
		if(!$type) {
			$info = pathinfo($file);
			$type = $info['extension'];
		}

		$folder = ($type == 'js') ? $this->js_folder : $this->css_folder ;
		$link = '';

		if($use_exact_path) $link = $file;
		elseif(file_exists(joinPath(App::$config['app_folder'], $file))) {
			$link = joinPath(App::$config['app_url'], $file);

		} elseif(file_exists(joinPath(App::$config['app_folder'], $folder, $file))) {
			$link = joinPath(App::$config['app_url'], $folder, $file);
		} else {
			$error = "<!-- Error: Can't find the resource file $file($type) -->";
			if($type === "css") array_push($this->css_includes, $error);
			if($type === "js") array_push($this->js_includes, $error);
			return;
		}

		if($type=='css' or $type=='stylesheet' or $type=='style' or $type=='stylesheets') {
			$current_include = '<link href="' . $link . '" type="text/css" rel="stylesheet" />';
			if(!in_array($current_include,$this->css_includes)) array_push($this->css_includes, $current_include);

		} elseif($type=='js' or $type=='javascript' or $type=='jscript' or $type=='script') {
			$current_include = '<script src="' . $link . '" type="text/javascript"></script>';
			if(!in_array($current_include,$this->js_includes)) array_push($this->js_includes, $current_include);

		} else {
			App::error("Template Error: Type(2nd argument of addResource) '$type' not valid. Must be 'css' or 'js'.");
		}

		if(!in_array($current_include,$this->includes)) {
			array_push($this->includes,$current_include);
		}
	}
	
	/**
	 * Adds meta tags. :TODO: improve this.
	 */
	function addMetadata($name,$contents) {
		$this->content['head'] .= '<meta name="'.$name.'" content="'.$contents.'" />'."\n";
	}

	//////////////////////////////// Layout Functions ////////////////////////////////
	/**
	 * Prints the page portion. This is done by including 'page.php' inside the 'layout' folder.
	 * Arguments: $variable_array - an array of varibale that should be availble in the included scope. OPTIONAL
	 */
	function printLayout($variable_array = false) {
		if($variable_array) extract($variable_array);

		$app_config = App::$config;
		if($app_config['iframe_backward_compatible']) {
			extract($GLOBALS);
		}

		$title = ($this->title) ? $this->title : App::$config['app_name'];
		$includes = implode("\n",$this->includes);
		$css_includes = implode("\n",$this->css_includes);
		$js_includes = implode("\n",$this->js_includes);

		// If the App does have a layout file.
		if(file_exists(joinPath(App::$config['app_folder'], $this->options['layout_file']))) {
			include(joinPath(App::$config['app_folder'], $this->options['layout_file']));
		
		} else {
			include(__DIR__ . '/../../templates/layout/page.php');
		}
	}
	
	//////////////////////////////// Action functions ////////////////////////////////
	/**
	 * Render the page by includeing the template file. It also calls the printHead() and printEnd() functions.
	 * Arguments : $template_file - The template file for the current page - if nothing is given it uses the 
	 *					template with the same name as the current page. OPTIONAL 
	 *				$use_layout - If this is true, it will insert the template file into the layout file and display it. If false, it will just display the template file.
	 *				$use_exact_path - If this is false, MVC will try to find the template file using some rules(search in the templates folder etc. If true, it will just include the exact page.
	 *				$variable_array - an array of varibale that should be availble in the included scope. OPTIONAL
	 */
	function render($variable_array = false, $options = []) {
		if($variable_array) extract($variable_array);

		$template_file = i($options, 'template');
		$use_layout = i($options, 'use_layout', true);
		$use_exact_path = i($options, 'use_exact_path', false);

		$app_config = App::$config;
		if($app_config['iframe_backward_compatible']) {
			extract($GLOBALS); //Make sure that all variables are accessable from the template.
		}

		if($template_file) {
			$this->setTemplate($template_file, $use_exact_path);
		} elseif(!$this->template) {
			$this->setTemplate($this->page);
		}
	
 		if(!$this->template) {
 			if($template_file != 'crud' or !isset($crud)) { // Couldn't find the page. And there is no crud rendering to do.
	 			App::error('The template file for "' . $this->page . '" does not exist.', '');
 			}
 		}

		if($use_layout and $this->options['insert_layout']) $this->printLayout($variable_array);
		else { // Don't use a layout.
			$title = ($this->title) ? $this->title : App::$config['app_name'];
			$app_config = App::$config;
			$includes = implode("\n",$this->includes);
			$css_includes = implode("\n",$this->css_includes);
			$js_includes = implode("\n",$this->js_includes);

			include($this->template);
		}
		App::$plugin->callHook('end');
	}
}
