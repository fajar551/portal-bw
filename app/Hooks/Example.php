<?php

namespace App\Hooks;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class Example
{
	/**
	 * Ini adalah example untuk handle hook
	 * 
	 * Hooks return harus salah satu dari: void, string, array
	 * 
	 * @param Event $event merupakan event yang sudah tersedia di App\Events dan mengembalikan object
	 * @return Void|String|Array
	 */
	public function handle(\App\Events\EmailPreSend $event)
	{
		/**
		 * @return void
		 * 
		 * berarti tidak mengembalikan apapun
		 * dipakai misalnya untuk logging, call api, dll.
		 */

		/**
		 * @return string
		 * 
		 * mengembalikan string
		*/
		// return "ini hooks return";

		/**
		 * @return array
		 * 
		 * mengembalikan array
		 */
		return [
			'abortsend' => false,
		];

		/**
		 * @return view
		 * 
		 * mengembalikan view html,js,css,etc...
		 */
		// return view("namafile"); // blade
	}
}
