<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CreateAPIRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create API route automaticly';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        $path = base_path("routes/api/{$name}.php");

        $controller_path = Str::camel($name);
        $controller_path = Str::ucfirst($controller_path);

        if (!File::exists($path)) {
            $controllerfilename = "{$controller_path}Controller";
            Artisan::call("make:controller API/{$controller_path}/{$controllerfilename}");

            $params = [
                'path' => $controller_path,
                'controller' => $controllerfilename,
            ];
            
            File::put($path, self::fileSourceController($params));

            return $this->info("API Route {$name} successfuly created.");
        } else {
            return $this->error("API Route {$name} already exists.");
        }
    }

    private static function fileSource($params = [])
    {
        $file = "<?php\n\n";
        $file .= "use App\Helpers\ResponseAPI;\n";
        $file .= "use Illuminate\Http\Request;\n";
        $file .= "use Illuminate\Support\Facades\Route;\n\n";
        
        $file .= "/*\n";
        $file .= "|--------------------------------------------------------------------------\n";
        $file .= "| API Routes\n";
        $file .= "|--------------------------------------------------------------------------\n";
        $file .= "|\n";
        $file .= "| Here is where you can register API routes for your application. These\n";
        $file .= "| routes are loaded by the RouteServiceProvider within a group which\n";
        $file .= "| is assigned the 'api' middleware group. Enjoy building your API!\n";
        $file .= "|\n";
        $file .= "*/\n\n";

        $file .= 'Route::get(\'/\', function (Request $request) {' . "\n";
        $file .= "\treturn ResponseAPI::Success([\n";
        $file .= "\t\t'data' => []\n";
        $file .= "\t]);\n";
        $file .= "});\n";
        $file .= "\n";

        return $file;
    }

    private static function fileSourceController($params = [])
    {
        $file = "<?php\n\n";
        $file .= "use App\Helpers\ResponseAPI;\n";
        $file .= "use Illuminate\Http\Request;\n";
        $file .= "use Illuminate\Support\Facades\Route;\n\n";
        
        $file .= "/*\n";
        $file .= "|--------------------------------------------------------------------------\n";
        $file .= "| API Routes\n";
        $file .= "|--------------------------------------------------------------------------\n";
        $file .= "|\n";
        $file .= "| Here is where you can register API routes for your application. These\n";
        $file .= "| routes are loaded by the RouteServiceProvider within a group which\n";
        $file .= "| is assigned the 'api' middleware group. Enjoy building your API!\n";
        $file .= "|\n";
        $file .= "*/\n\n";

        $file .= "Route::namespace('API\\".$params['path']."')->group(function () {\n";
        $file .= "\tRoute::get('/', '{$params['controller']}@index');\n";
        $file .= "});\n";

        return $file;
    }
}
