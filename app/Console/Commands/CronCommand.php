<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:run {*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command cron';

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
        // $application = new \App\Cron\Console\Application("CBMS Automation Task Utility", WHMCS\Application::FILES_VERSION);
        $application = new \App\Cron\Console\Application("CBMS Automation Task Utility", "7.8.0-rc.1");
        $application->setAutoExit(false);
        if (\App\Helpers\Php::isCli()) {
            $input = new \App\Cron\Console\Input\CliInput();
            if ($input->isLegacyInput()) {
                $input = new \App\Cron\Console\Input\CliInput($input->getMutatedLegacyInput());
            }
            $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        }
        define("INCRONRUN", true);
        define("IN_CRON", true);
        $exitCode = $application->run($input, $output);
        if ($output instanceof \Symfony\Component\Console\Output\BufferedOutput) {
            $config = config("portal");
            if (isset($config['config']['display_errors']) && !empty($config['config']["display_errors"])) {
                echo nl2br($output->fetch());
            }
        }
        exit($exitCode);
    }
}
