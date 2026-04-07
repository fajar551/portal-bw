<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use DB;

class CallbackController extends Controller
{
    //
    public function register($controller, $method)
    {
        DB::beginTransaction();
        try {
            $result = App::call("App\\Http\\Controllers\\Callback\\"."$controller@{$method}");
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                "message" => $e->getMessage(),
            ], 400);
        }
    }
}
