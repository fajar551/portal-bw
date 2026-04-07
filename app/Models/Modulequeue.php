<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Modulequeue extends Model
{
	protected $table = 'modulequeue';
	protected $primaryKey = "id";
	protected $casts = array("last_attempt" => "datetime");
	protected $dates = array("last_attempt");
	protected $fillable = array("service_type", "service_id", "module_name", "module_action", "completed");
	protected $columnMap = array("lastAttempt" => "last_attempt", "lastAttemptError" => "last_attempt_error");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public static function add($serviceType, $serviceId, $module, $moduleAction, $lastAttemptError)
	{
		if (defined("NO_QUEUE") && NO_QUEUE == true) {
			return true;
		}
		if (is_null($lastAttemptError)) {
			$lastAttemptError = "";
		}
		$queue = self::firstOrNew(array("service_type" => $serviceType, "service_id" => $serviceId, "module_name" => $module, "module_action" => $moduleAction, "completed" => 0));
		$queue->last_attempt = \Carbon\Carbon::now();
		$queue->last_attempt_error = $lastAttemptError;
		if ($queue->exists) {
			$queue->num_retries++;
		} else {
			$queue->num_retries = 0;
		}
		return $queue->save();
	}

	public static function resolve($serviceType, $serviceId, $module, $moduleAction)
	{
		$queue = self::whereServiceType($serviceType)->whereServiceId($serviceId)->whereModuleName($module)->whereModuleAction($moduleAction)->whereCompleted(0)->first();
		if ($queue) {
			$queue->completed = 1;
			$queue->last_attempt = \Carbon\Carbon::now();
			return $queue->save();
		}
		return true;
	}
}
