<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CreateHooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hook:make {name} {--E|event=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a Hooks with following Event';

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
        $event = $this->option('event');

        $path = app_path() . "/Hooks/{$name}.php";

        if (!File::exists($path)) {
            $params = [
                'className' => $name,
                'event' => $event,
            ];
            File::put($path, self::fileSource($params));

            return $this->info("Hook {$name} successfuly created.");
        } else {
            return $this->error("Hook {$name} already exists.");
        }
    }

    private static function fileSource($params = [])
    {
        $file = "<?php\n\n";
        $file .= "namespace App\Hooks;\n\n";
        
        if (isset($params['event'])) {
            $file .= "use App\Events\\". $params['event'] .";\n";
        }

        $file .= "use Illuminate\Support\Facades\Request;\n\n";
        $file .= "class {$params['className']}\n";
        $file .= "{\n";
        
        if (isset($params['event'])) {
            $file .= "\tpublic function handle({$params['event']} ". '$event' .")\n";
        } else {
            $file .= "\tpublic function handle()\n";
        }
        
        $file .= "\t" . "{\n";
        $file .= "\t\t" . '//' . "\n";
        $file .= "\t" . "}\n";
        $file .= "}\n";

        return $file;
    }
}
