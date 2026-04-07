<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\Cfg;
use App\Helpers\LogActivity;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Hooks
{
	/**
	 * run_hook
	 */
	public static function run_hook($hook_name, array $args = [], $unpackArguments = false)
	{
		// TODO: check license
		global $hooks;
		if (!is_array($hooks)) {
			self::hook_log($hook_name, "Hook File: the hooks list has been mutated to %s", ucfirst(gettype($hooks)));
			$hooks = array();
		}
		self::hook_log($hook_name, "Called Hook Point %s", $hook_name);
		$results = array();

		$className = "\\App\\Events\\{$hook_name}";
		if (!class_exists($className)) {
			self::hook_log($hook_name, "Hook Function %s Not Found", $hook_name);
			$results = array();
		} else {
			$hookReturn = event(new $className($args));
			$results = $hookReturn;
		}

		return array_filter($results);
	}

	/**
	 * hook_log
	 */
	public static function hook_log($hook_name, $msg, $input1 = "", $input2 = "", $input3 = "")
	{
		if ($hook_name == "LogActivity") {
			return NULL;
		}
		$HooksDebugMode = Cfg::getValue("HooksDebugMode");
		$hookLogging = defined("HOOKSLOGGING") || $HooksDebugMode;
		if ($hookLogging) {
			// TODO: $specificHookLogging = App::getApplicationConfig()->hooks_debug_whitelist;
			$specificHookLogging = [];
			if ($specificHookLogging && is_array($specificHookLogging) && 0 < count($specificHookLogging) && !in_array($hook_name, $specificHookLogging)) {
				$hookLogging = false;
			}
			if ($hookLogging) {
				$msg = "Hooks Debug: " . $msg;
				if (defined("IN_CRON")) {
					$msg = "Cron Job: " . $msg;
				}
				LogActivity::Save(sprintf($msg, $input1, $input2, $input3));
			}
		}
	}

	public static function run_validate_hook(&$validate, $hook_name, $args)
	{
		$hookerrors = self::run_hook($hook_name, $args);
		$errormessage = "";
		if (is_array($hookerrors) && count($hookerrors)) {
			foreach ($hookerrors as $hookerrors2) {
				if (is_array($hookerrors2)) {
					$validate->addErrors($hookerrors2);
				} else {
					$validate->addError($hookerrors2);
				}
			}
		}
	}

	public static function processHookResults($moduleName, $function, array $hookResults = array())
	{
		if (!empty($hookResults)) {
			$hookErrors = array();
			$abortWithSuccess = false;
			foreach ($hookResults as $hookResult) {
				if (!empty($hookResult["abortWithError"])) {
					$hookErrors[] = $hookResult["abortWithError"];
				}
				if (array_key_exists("abortWithSuccess", $hookResult) && $hookResult["abortWithSuccess"] === true) {
					$abortWithSuccess = true;
				}
			}
			if (count($hookErrors)) {
				throw new \Exception(implode(" ", $hookErrors));
			}
			if ($abortWithSuccess) {
				LogActivity::Save("Function " . $moduleName . "->" . $function . "() Aborted by Action Hook Code");
				return true;
			}
		}
		return false;
	}

	public static function get_registered_hooks($hookName)
	{
		global $hooks;
		if (is_array($hooks) && isset($hooks[$hookName]) && is_array($hooks[$hookName])) {
			return $hooks[$hookName];
		}
		return array();
	}

	public static function add_hook($hook_name, $priority, $hook_function, $rollback_function = "")
	{
		global $hooks;
		if (!is_array($hooks)) {
			self::hook_log($hook_name, "Hook File: the hooks list has been mutated to %s", ucfirst(gettype($hooks)));
			$hooks = array();
		}
		if (!array_key_exists($hook_name, $hooks)) {
			$hooks[$hook_name] = array();
		}
		array_push($hooks[$hook_name], array("priority" => $priority, "hook_function" => $hook_function, "rollback_function" => $rollback_function));
		// self::hook_log($hook_name, "Hook Defined for Point: %s - Priority: %s - Function Name: %s", $hook_name, $priority, hooktostring($hook_function));
		uasort($hooks[$hook_name], "sort_array_by_priority");
	}
}
