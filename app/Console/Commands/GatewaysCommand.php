<?php

namespace App\Console\Commands;

use DB;
use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class GatewaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateways:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Gateway module';

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

        Config::set('modules.namespace', 'Modules\\Gateways');
        Config::set('modules.paths.modules', base_path('Modules/Gateways'));
        // Config::set('modules.paths.generator.routes.generate', false);

        Artisan::call("module:make {$name}");
        // Artisan::call("module:make-controller {$name}Controller {$name}");
        // Artisan::call("module:make-provider {$name}ServiceProvider {$name}");

        $module = \Module::findOrFail($name);
        $module->disable();

        return $this->info("Gateway module {$name} created successfuly");
    }
}
