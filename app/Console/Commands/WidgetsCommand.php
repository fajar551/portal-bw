<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class WidgetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'widgets:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Widget module';

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
        $name = Str::camel($name);
        $name = Str::ucfirst($name);

        Config::set('modules.namespace', 'Modules\\Widgets');
        Config::set('modules.paths.modules', base_path('Modules/Widgets'));

        Artisan::call("module:make {$name}");
        // Artisan::call("module:make {$name} -p");
        // Artisan::call("module:make-controller {$name}Widget {$name}");

        return $this->info("Widgets module {$name} created successfuly");
    }
}
