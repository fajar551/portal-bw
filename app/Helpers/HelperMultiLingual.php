<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HelperMultiLingual
{

	public static function get(){
		$lang=[
					'Arabic' 			=> 'arabic',
					'Azerbaijani' 		=> 'azerbaijani',
					'Catalan' 			=> 'catalan',
					'Chinese' 			=> 'chinese',
					'Croatian' 			=> 'croatian',
					'Czech' 				=> 'czech',
					'Danish' 			=> 'danish',
					'Dutch' 				=> 'dutch',
					'English' 			=> 'english',
					'Estonian' 			=> 'estonian',
					'Farsi' 				=> 'farsi',
					'French' 			=> 'french',
					'German' 			=> 'german',
					'Hebrew' 			=> 'hebrew',
					'Hungarian' 		=> 'hungarian',
					'Indonesia' 		=> 'indonesia',
					'Italian' 			=> 'italian',
					'Macedonian' 		=> 'macedonian',
					'Portuguese-br' 	=> 'portuguese-br',
					'Portuguese-pt' 	=> 'portuguese-pt',
					'Romanian' 			=> 'romanian',
					'Russian' 			=> 'russian',
					'Spanish' 			=> 'spanish',
					'Swedish' 			=> 'swedish',
					'Turkish' 			=> 'rurkish',
					'Ukranian' 			=> 'ukranian',
		];

		return $lang;
	}

}
