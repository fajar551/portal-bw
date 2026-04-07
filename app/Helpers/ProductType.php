<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductType
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public static function getName($name = '')
	{
		switch ($name) {
			case 'hostingaccount':
				return 'Hosting Account';
				break;

			case 'server':
				return 'Dedicated/VPS Server';
				break;

			case 'other':
				return 'Other Product/Service';
				break;
			
			case 'sharedhosting':
				return 'Shared Hosting';
				break;
			
			case 'resellerhosting':
				return 'Reseller Hosting';
				break;

			default:
				return 'unknown';
				break;
		}
	}

}
