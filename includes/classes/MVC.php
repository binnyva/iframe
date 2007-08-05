<?php
/** *************************************************************************
 * File		: classes/MVC.php
 * Version	: $Id: MVC.php,v 1.2 2007/03/08 17:37:05 binnyva Exp $
 * This file holds all the templating actions. 
 *
 ****************************************************************************/
$template = new MVC();

/**
 * Class : MVC
 * All MVC action are in this class - Templating and Model, anyway
 */
class MVC {
	var $page		= ""; ///This variable holds the location of the controller page.
	var $template	= ""; ///Holds the location of the view(or template) page. This value can be deduced from the controller page or can be explicitly set using the setTemplate() funciton.
	var $model		= "";
	var $model_name = "";
	var $controller = "";

	var $title		= ""; ///The title of the page - use setTemplate() to set it.
	var $layout		= 'page.php'; ///This should contain the layout of the site.
	var $options	= array(
		'template_folder'	=> 'templates',
		'model_folder'		=> 'models',
		'insert_layout'		=> true
	);
	var $includes	= array();
	var $css_folder = 'css/';
	var $js_folder	= 'js/';

	/**
	 * Constructor
	 * Finds the location of the current file - eg. 'advertise/myaccount.php'
	 */
	function MVC() {
		$this->setPage($_SERVER["PHP_SELF"]);
		$this->setController();
		$this->setModel();
	}
	
	/////////////////////////////// Setting functions ////////////////////////////////
	/**
	 * Takes a file name as the argument and find its template based on the file name.
	 * Argument : $file_name - The file name of the controller document.
	 */
	function setPage($file_name) {
		global $config;
		//Remove the absolute site path while conserving the directory structure in the URL
		$file_name = str_replace($_SERVER['DOCUMENT_ROOT'],'',$file_name);
		
		$escaped_path = preg_replace('/([\/\\\.\?\~\\=\_\-\,])/','\\\$1',$config['absolute_path']);
		$file_name = preg_replace('/' . $escaped_path . '/','',$file_name,1); //Replace just 1 time
		$file_name = preg_replace('/controllers\//','',$file_name);//If we are following mvc architecture
		if(strpos($file_name,'/') === 0) $file_name = substr($file_name,1);

		//Remove the query parts		
		$parts = explode('?',$file_name);
		$file_name = $parts[0];
		//Make sure that it is a file call and not a directory
		if(!strpos(basename($file_name),'.php')) { ///:TODO: This could be .php5 or something - could cause problems.
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
	 */
	function setTemplate($template_file) {
		$this->_findResources($template_file);

		$template_file = $GLOBALS['rel'] . $this->options['template_folder'] . '/' . $template_file;
		
		//Plugins are a special case.
		if(strpos($_SERVER['PHP_SELF'],'plugins') !== false) {
			$template_file = 'template.php';
		}
		
		if(!file_exists($template_file)) {
			//Search the template folder for that file
			if (file_exists( $this->options['template_folder'] . '/' . $template_file))
				$this->template = $this->options['template_folder'] . '/' . $template_file;
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
		$model_folder = $GLOBALS['rel'] . $this->options['model_folder'] . '/';
		$model_name = '';
	
		if(!file_exists($model_file)) {
			//Search the template folder for that file
			if (file_exists( $model_folder . $model_file)) {
				$this->model = $model_folder . $model_file;
				$model_name = $model_file;
			} else {
				//Try to find the model with newer methods.
				//First, try to make the first letter uppercase
				if (file_exists( $model_folder . ucfirst($model_file) )) {
					$this->model = $model_folder . ucfirst($model_file);
					$model_name = ucfirst($model_file);
				} else {
					//Go thru the model folder and find all the files, then make it all lower case and see if one matchs the current controller.
					$all_models = ls($model_folder);
					foreach($all_models as $mod) {
						if(strtolower($mod) == strtolower($this->controller) . '.php') {
							$this->model = $mod;
							$model_name = $mod;
							break;
						}
					}
				}
			}
		}
		else $this->model = $model_file;

		if($this->model) {
			include($model_folder . $this->model);
			$this->model_name = str_replace('.php','',$model_name);
		}
	}
	
	/**
	 * Finds all the CSS and JS files that must be included in this page.
	 */
	function _findResources($template_file) {
		global $rel;
		$css_file = preg_replace('/.php$/','.css',$template_file);
		$js_file = preg_replace('/.php$/','.js',$template_file);
		
		if(file_exists($rel . $this->css_folder . $css_file)) $this->addResource( $css_file , 'css' , true);
		if(file_exists($rel . $this->js_folder . $js_file)) $this->addResource( $js_file , 'js' , true);
	}

	/**
	This will set the title of the page.
	Argument : $title - The string to be used inside the <title></title> tag.
	*/
	function setTitle($title) {
		if($title) $this->title = $title;
	}

	/**
	 * Add a resource file to the HTML page - like a css file or a javascript file.
	 * Arguments :	$file - The file to be included in the output HTML file
	 *				$type - The type of file - this says whether the file is a javascript or a css file. Must have the value('css' or 'js')
	 *				$force(Boolean) - The file existance check will not be done if this is true.
	 */
	function addResource($file,$type="",$force=false) {
		if(!$file) return;
		global $rel,$config;
		if(!$type) list($name,$type) = explode(".",$file);
		$folder = ($type == 'js') ? $this->js_folder : $this->css_folder ;
		
		//Make sure that the file exists
		if($force) {
			if(file_exists($rel . $folder .  $file)) {
				$file = $folder . $file;
			} else {
				error("Template Error: File Include Error - '$rel$folder/$file' does not exists(Current folder : ".getcwd().")",__FILE__,__LINE__);
			}
		} else $file = $folder . $file;

		if($type=='css' or $type=='stylesheet' or $type=='style' or $type=='stylesheets') {
			$current_include = '<link href="' . $config['url'] . $file . '" type="text/css" rel="stylesheet" />';

		} elseif($type=='js' or $type=='javascript' or $type=='jscript' or $type=='script') {
			$current_include = '<script src="' . $config['url'] . $file . '" type="text/javascript"></script>';

		} else {
			error("Template Error: $type not defined");
		}

		if(!in_array($current_include,$this->includes)) {
			array_push($this->includes,$current_include);
		}
	}
	
	/**
	 * Adds meta tags. :TODO: improve this.
	 */
	function addMetadata($name,$contents) {
		$this->head_data .= '<meta name="'.$name.'" content="'.$contents.'" />'."\n";
	}


	//////////////////////////////// Layout Functions ////////////////////////////////
	/**
	 * Prints the page portion. This is done by including 'page.php' inside the 'layout' folder.
	 */
	function printLayout() {
		extract($GLOBALS);

		$title = ($this->title) ? $this->title : $config['site_title'];
		$includes = implode($this->includes,"\n");

		include($rel . $this->options['template_folder'] . "/layout/" . $this->layout);
	}
	
	//////////////////////////////// Action functions ////////////////////////////////
	/**
	 * Render the page by includeing the template file. It also calls the printHead() and printEnd() functions.
	 * Arguments : $template_file - The template file for the current page - if nothing is given it uses the 
	 *					template with the same name as the current page. OPTIONAL
	 */
	function render($template_file = "") {
		extract($GLOBALS); //Make sure that all variables are accessable from the template.

		if($template_file) {
			$this->setTemplate($template_file);
		} elseif(!$this->template) {
			$this->setTemplate($this->page);
		}
		
 		if(!$this->template) error('The template file "' . $this->template . '" does not exist.');
		
		if($this->options['insert_layout']) $this->printLayout();
		else include($this->template);
	}
}