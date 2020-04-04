<?php
namespace iframe\Development;
use iframe\DB\Sql;

/**
 * Logging Class. This can be used to log debug message to an external text file. By default, the text file is [Project Folder]/Logs/Development.log file.
 * Example: $Logger = new Logger;
 * 			$Logger->log("My Debug Message");
 * 	OR
 * 			$Logger = new Logger('Log', 'mysql', 'CSVGo');
 * 			$Logger->log("Called 'student_data'", 'info');
 */
class Logger {
	private $log_destination = '';
	private $log_type = 'file';
	private $log_group = '';
	private $handle = false;
	private $debug = false;
	
	/**
	 * Constructor
	 * Argument: $log_destination - The file/table to which all the log message must be saved to.
	 * 			 $log_type: file/mysql - What kind of log should be maintained. Right now only file and mysql are supported.
	 * 			 $log_group - usually the app name that logs. Useful for grouping all the logs together.
	 */
	function __construct($log_destination = '', $log_type = 'file', $log_group = '') {
		global $config;
		
		if($log_type == 'file') {
			$folder = joinPath($config['site_folder'],'Logs');
			if(!$log_destination) { //Log file not specifed - use default.
				if(file_exists($folder)) $log_destination = joinPath($folder, 'Development.log');
			
			} else { //Use user specified log file
				if(file_exists($folder)) $log_destination = joinPath($folder, $log_destination);
			}

			if($this->log_destination and is_writable($folder)) {
				$this->handle = fopen($this->log_destination, 'a');
			} 
			
			if(!$this->handle and $this->debug) print "Cannot enable logging: Log File '{$this->log_destination}' not writable";
		} elseif($log_type == 'mysql') {
			global $config;
			if(isset($config['db_host'])) {
				$this->handle = new Sql($config['db_host'], $config['db_user'], $config['db_password'], $config['db_database']);
			}
		} else {
			if($this->debug) print "Unknown Log Type '$log_type'. So far, only 'file' and 'mysql' supported.";
		}
		
		$this->log_destination = $log_destination;
		$this->log_type = $log_type;
		$this->log_group = $log_group;
	}
	
	/**
	 * Append the log to the log file.
	 * Argument: $message - The text that should be logged.
	 */
	function log($message, $level = 'info', $group = '') {
		if(!$this->handle) return false;

		if(!$group) $group = $this->log_group;

		if($this->log_type == 'file') {
			if($group) $message = $group . ': ' . $message;
			fwrite($this->handle, $message . "\n");
		} elseif($this->log_type == 'mysql') {
			$this->handle->insert($this->log_destination, array(
				'name'	=> $group,
				'log'	=> $message,
				'added_on' => 'NOW()',
				'level'	=> $level
			));
		} else {
			return false;
		}

		return true;
	}
	
	/**
	 * Close the file handle. You will not be able to write anything after this function is called.
	 */
	function close() {
		if(!$this->handle) return false;

		if($this->log_type == 'file') fclose($this->handle);
		elseif($this->log_type == 'mysql') $this->handle->disconnect();
	}
}
