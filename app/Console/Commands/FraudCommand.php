<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class FraudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fraud:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Fraud module';

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

        Config::set('modules.namespace', 'Modules\\Fraud');
        Config::set('modules.paths.modules', base_path('Modules/Fraud'));

        Artisan::call("module:make {$name} -p");
        Artisan::call("module:make-controller {$name}Controller {$name}");

        return $this->info("Fraud module {$name} created successfuly");
    }
}
