<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AddonsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addons:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Addon module';

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

        Config::set('modules.namespace', 'Modules\\Addons');
        Config::set('modules.paths.modules', base_path('Modules/Addons'));

        Artisan::call("module:make {$name}");

        $module = \Module::findOrFail($name);
        $module->disable();

        return $this->info("Addon module {$name} created successfuly");
    }
}
