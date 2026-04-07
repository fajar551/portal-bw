<?php

namespace App\Http\Middleware;

use Closure;

class GlobalVariableLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->setGlobalVariableLanguageAdmin();
        $this->setGlobalVariableLanguageClient();
        $this->setGlobalVariableConfig();
        $this->setGlobalVariableCurrency();
        return $next($request);
    }

    /**
     * Global variable language
     * untuk dipakai di API dan admin area
     */
    public function setGlobalVariableLanguageAdmin($filename = 'admin')
    {
        $languages = \Lang::get($filename);
        if (!is_array($languages)) {
            throw new \Exception("Language {$filename} can't load. Please check file source");
            
        }
        $data = [];
        foreach ($languages as $key => $lang) {
            $data[$key] = $lang;
        }
        $GLOBALS['_LANG'] = $data;
    }

    /**
     * Global variable language
     * untuk dipakai di API dan admin area
     */
    public function setGlobalVariableLanguageClient($filename = 'client')
    {
        $languages = \Lang::get($filename);
        if (!is_array($languages)) {
            throw new \Exception("Language {$filename} can't load. Please check file source");
            
        }
        $data = [];
        foreach ($languages as $key => $lang) {
            $data[$key] = $lang;
        }
        $GLOBALS['_LANG'] = $data;
    }

    /**
     * Global variable config
     * untuk dipakai di API dan admin area
     */
    public function setGlobalVariableConfig()
    {
        $configs = \App\Models\Configuration::all();
		$data = [];
        foreach ($configs as $config) {
            $data[$config->setting] = $config->value;
        }
        $GLOBALS['CONFIG'] = $data;
    }

    /**
     * Global variable currency
     * untuk dipakai di API dan admin area / client area
     */
    public function setGlobalVariableCurrency($userid = "", $cartcurrency = "")
    {
       $curr = (new \App\Helpers\AdminFunctions())->getCurrency($userid, $cartcurrency);
       $GLOBALS['currency'] = $curr;
    }
}
