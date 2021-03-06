<?php
namespace iframe\iframe;

class Plugin {
	private $plugins = array();
	private $hooks = array();

	private $available_hooks = array(
	// Hook Name	Called at...
		'init',		// At the very start. At the end of App::bootstrap
		'end',		// At the end - at the end of the render() function.
	);

	public function __construct($plugin_folder) {
		if($plugin_folder == false) return false;
		
		$plugins = ls("*",$plugin_folder,false,array('return_folders'));
		foreach ($plugins as $current_plugin_folder) {
			if(file_exists(joinPath($plugin_folder, $current_plugin_folder, "hooks.php"))) {
				include(joinPath($plugin_folder, $current_plugin_folder, "hooks.php"));
			}
		}
	}

	/* Calls/Activate the hooks. This will call all the functions associated with the hook.
	 * Argument: $hook_name - The name of the hook to be activated.
	 */
	function callHook($hook_name, $arguments = array(), $return_data = false) {
		if(empty($this->hooks[$hook_name])) return;

		foreach ($this->hooks[$hook_name] as $function_name) {
			$return = call_user_func_array($function_name, array_values($arguments));
			if($return_data) return $return; // If Return is there, for loop will run only once.
		}
	}

	/* Add Hooks to the plugin system.
	 * Argument: $hook_name - the name of the hook
	 *			 $function_name - the name of the function that should be called when the hook is activated.
	 */
	function addHook($hook_name, $function_name) {
		if(!isset($this->hooks[$hook_name])) {
			$this->hooks[$hook_name] = array();
		}

		array_push($this->hooks[$hook_name], $function_name);
	}

	/* Check for the existance of a given hook.
	 * Argument: $hook_name - the name of the hook
	 */
	function isHook($hook_name) {
		return isset($this->hooks[$hook_name]);
	}
}
