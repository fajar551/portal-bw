<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Configuration;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Get configuration value
 * 
 */
class Cfg
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * get
	 * 
	 * Get the configuration value
	 * 
	 * @param $key string|required
	 * 
	 * @return $config or null if the config doen't exist
	 * 
	 */
	public static function get($key = '')
	{
		$config = Configuration::where('setting', $key)->first();
		return $config ? $config->value : null;
	}

	/**
	 * getValue
	 */
	public static function getValue($key = '')
	{
		return self::get($key);
	}

	/**
	 * setValue
	 */
	public static function setValue($key = '', $value = '')
	{
		if ($key) {
			$config = Configuration::updateOrCreate(
				['setting' => $key],
				['value' => $value]
			);

			return $config->save();
		}

		return false;
	}

	/**
	 * Set the new config or update existing by the key
	 * 
	 * @param $key string|required
	 * @param $value string|optional
	 * 
	 * @return boolean true|false
	 * 
	 */
	public static function set($key, $value)
	{
		if ($key) {
			$config = Configuration::updateOrCreate(
				['setting' => $key],
				['value' => $value]
			);

			return $config->save();
		}

		return false;
	}

}
