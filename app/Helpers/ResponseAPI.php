<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ResponseAPI
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public static function Send($status = '', $payload = [], $code = 200)
	{
		$response = [
			'result' => $status,
		];
		
		$responseMerge = array_merge($response, $payload);

		return response()->json($responseMerge, $code);
	}

	public static function Error($payload = [], $code = 200)
	{
		return self::Send('error', $payload, $code);
	}

	public static function Success($payload = [], $code = 200)
	{
		return self::Send('success', $payload, $code);
	}
}
