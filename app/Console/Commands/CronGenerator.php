<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:generate {name} {--signature=} {--description=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate cron command';

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
        $signature = $this->option('signature');
        $description = $this->option('description');

        if (!$signature) {
            return $this->error("Signature required. --signature=");
        }

        $this->cron($name, $signature, $description);

        return $this->info("Cron generated successfully");
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function cron($name, $signature, $description = "Cron Description")
    {
        $cronTemplate = str_replace(
            ['{{CronName}}', '{{signature}}', '{{description}}'],
            [$name, $signature, $description],
            $this->getStub('Cron')
        );

        $path = app_path("Console/Crons/{$name}.php");
        file_put_contents($path, $cronTemplate);
    }
}
