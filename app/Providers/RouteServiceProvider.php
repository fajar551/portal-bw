<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';
    public const HOME_ADMIN = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->mapAllRoutesAPI();

        parent::boot();
    }

    protected function mapAllRoutesAPI()
    {
        $path = base_path('routes/api');
        $files = File::files($path);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (Str::endsWith($filename, '.php')) {
                $filenameWithoutExtension = Str::replaceLast('.php', '', $filename);
                Route::prefix("api/{$filenameWithoutExtension}")
                    ->middleware(['auth.basic:admin', 'apiallowedips'])
                    ->namespace($this->namespace)
                    ->group(base_path("routes/api/{$filename}"));
            }
        }
    }

    protected function mapRoutesAPIIncludes()
    {
        Route::post("includes/api.php", "App\Http\Controllers\API\Includes@api");
        // ->middleware(['auth.basic:admin', 'apiallowedips']);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        // custom routes
        $this->mapAdminRoutes();
        $this->mapRoutesAPIIncludes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        $defaultTheme = \App\Helpers\Cfg::get('Template');

        Route::middleware(['web', "theme:$defaultTheme"])
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('apiconsumer')
            ->as('apiconsumer.')
            ->middleware('apiconsumer')
            ->namespace($this->namespace)
            ->group(base_path('routes/apiconsumer.php'));
    }

    /**
     * Define the "admin" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::middleware(['admin', 'theme:admin'])
            ->as('admin.') // route name
            ->prefix(env('ADMIN_ROUTE_PREFIX', 'admin'))
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }
}
