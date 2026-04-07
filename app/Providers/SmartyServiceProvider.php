<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Engines\SmartyEngine;

class SmartyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->app['view']->addExtension($this->app['config']->get('smarty.extension', 'tpl'), 'smarty', function() {
			return new SmartyEngine($this->app['config']);
		});
    }
}
