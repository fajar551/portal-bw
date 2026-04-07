<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class CreateNewHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'helper:make {name? : Name for your helper. Must be uniq}';
    // protected $signature = 'helper:make';
    protected $signature = 'helper:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new helper file in app/Helpers';

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
        $this->handle2();
    }

    private function handle1()
    {
        // $name = $this->argument('name');
        
        $name = $this->ask('What is helper name? e.g OrdersShipped (with camel case)');

        if ($this->confirm('Do you wish to continue?')) {
            // return $this->info($name);
            // create file in app/Helpers
            // check existing file name
            
            $path = app_path() . "/Helpers/{$name}.php";

            if (!File::exists($path)) {
                $source = app_path() . "/Helpers/Example.php";
                
                // File::copy($source, $path);
                $params = [
                    'className' => $name,
                ];
                File::put($path, self::fileSource($params));

                return $this->info("Helper {$name} successfuly created.");
            } else {
                return $this->error("Helper {$name} already exists.");
            }
        }

        // return 0;
        return $this->error('Helper name required.');
    }

    private function handle2()
    {
        $name = $this->argument('name');

        if ($name) {
            // return $this->info($name);
            // create file in app/Helpers
            // check existing file name
            
            $path = app_path() . "/Helpers/{$name}.php";

            if (!File::exists($path)) {
                $source = app_path() . "/Helpers/Example.php";
                
                // File::copy($source, $path);
                $params = [
                    'className' => $name,
                ];
                File::put($path, self::fileSource($params));

                return $this->info("Helper {$name} successfuly created.");
            } else {
                return $this->error("Helper {$name} already exists.");
            }
        }

        // return 0;
        return $this->error('Helper name required.');
    }

    private static function fileSource($params = [])
    {
        $file = "<?php\n";
        $file .= "namespace App\Helpers;\n\n";
        $file .= "// Import Model Class here\n\n";
        $file .= "// Import Package Class here\n\n";
        $file .= "// Import Laravel Class here\n";
        $file .= "use Illuminate\Support\Facades\Request;\n";
        $file .= "use Illuminate\Support\Str;\n";
        $file .= "use Illuminate\Support\Facades\Log;\n\n";
        $file .= "class {$params['className']}\n";
        $file .= "{\n";
        $file .= "\t" . 'protected $request;' . "\n\n";
        $file .= "\t" . 'public function __construct(Request $request)' . "\n";
        $file .= "\t" . "{\n";
        $file .= "\t\t" . '$this->request = $request;' . "\n";
        $file .= "\t" . "}\n\n";
        $file .= "\t" . "// Write static function here\n";
        $file .= "\n";
        $file .= "}\n";

        return $file;
    }
}
