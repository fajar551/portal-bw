<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class RegistrarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrar:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Registrar module';

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

        Config::set('modules.namespace', 'Modules\\Registrar');
        Config::set('modules.paths.modules', base_path('Modules/Registrar'));

        // other configs
        // Config::set('modules.paths.generator.test-feature.generate', false);
        // Config::set('modules.paths.generator.test.generate', false);
        // Config::set('modules.paths.generator.command.generate', false);
        // Config::set('modules.paths.generator.migration.generate', false);
        // Config::set('modules.paths.generator.seeder.generate', false);
        // Config::set('modules.paths.generator.factory.generate', false);
        // Config::set('modules.paths.generator.routes.generate', false);
        // Config::set('modules.paths.generator.filter.generate', false);
        // Config::set('modules.paths.generator.request.generate', false);
        // Config::set('modules.paths.generator.assets.generate', false);
        // Config::set('modules.paths.generator.lang.generate', false);
        // Config::set('modules.paths.generator.views.generate', false);

        Artisan::call("module:make {$name}");
        // Artisan::call("module:make {$name} -p");
        // Artisan::call("module:make-controller {$name}Controller {$name}");
        
        $module = \Module::findOrFail($name);
        $module->disable();

        return $this->info("Registrar module {$name} created successfuly");
    }
}
