<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use App\Compiler\MyBladeCompiler;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public function registerBladeEngine($resolver)
    {
        $this->app->singleton('blade.compiler', function () {
            return new MyBladeCompiler(
                $this->app['files'], $this->app['config']['view.compiled']
            );
        });

        $resolver->register('blade', function () {
            return new CompilerEngine($this->app['blade.compiler']);
        });
    }
}
