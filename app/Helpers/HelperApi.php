<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Request as Reg;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Auth, App;
class HelperApi
{

	public function __construct()
	{
		
	}

	public function localAPI($command = '', $params = [], $adminusername = "")
	{
		return self::post($command, $params);
	}

    public static function post($command = '', $params = [])
    {
        try {
            request()->merge($params);
            $route = Route::getRoutes()->getByName($command)->action;
            $response = App::call($route['controller']);
            return json_decode($response->content(), true);
        } catch (\Exception $e) {
            $response = \App\Helpers\ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
            return json_decode($response->content(), true);
        }
    }

	/*
		public static function  post
		@param $api String api name
		@param param array paramater
	*/
	
	public static function  postOLD($api='',Array $param=[]){
		$path=str_replace(url('/'),'',route($api));
		$request = Request::create($path, 'POST',$param);
				//fixed
				$request = Reg::create($path, 'POST',$param,$cookies=[],$files=[],Request::server());
				$res = app()->handle($request);
				$instance = json_decode($res->getContent());
        /* dd( $instance); */
		return $instance;
	}

	/*
		public static function  get
		@param $api String api name
		@param param array paramater
	*/

	public static function  get($api='',Array $param=[]){
		$path=str_replace(url('/'),'',route($api));
        $request = Request::create($path, 'GET',$param);
				//fixed
				$request = Reg::create($path, 'GET',$param,$cookies=[],$files=[],Request::server());
				$res = app()->handle($request);
				$instance = json_decode($res->getContent());
        /* dd( $instance); */
		return $instance;
	}


	
}
