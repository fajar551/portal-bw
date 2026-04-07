#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->boot();
$app->registerCoreContainerAliases();
// $app->withFacades();
$app->getBindings();

use Illuminate\Database\Capsule\Manager as Capsule;
/*
|--------------------------------------------------------------------------
| Run The Artisan Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers. Here goes nothing!
|
*/

// $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// $status = $kernel->handle(
//     $input = new Symfony\Component\Console\Input\ArgvInput,
//     new Symfony\Component\Console\Output\ConsoleOutput
// );

/*
|--------------------------------------------------------------------------
| Shutdown The Application
|--------------------------------------------------------------------------
|
| Once Artisan has finished running, we will fire off the shutdown events
| so that any final work may be done by the application before we shut
| down the process. This is the last thing to happen to the request.
|
*/

// $kernel->terminate($input, $status);
// // exit($status);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => false,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$app->bind(\App\Cron\Console\Application::class, function() {
    return new \App\Cron\Console\Application("CBMS Automation Task Utility", "7.8.0-rc.1");
});
$app->bind(\App\Cron\Console\Input\CliInput::class, function() {
    return new \App\Cron\Console\Input\CliInput();
});

// $application = new \App\Cron\Console\Application("CBMS Automation Task Utility", "7.8.0-rc.1");
$application = $app->make(\App\Cron\Console\Application::class);
$application->setAutoExit(false);
// if (\App\Helpers\Php::isCli()) {
    // $input = new \App\Cron\Console\Input\CliInput();
    $input = $app->make(\App\Cron\Console\Input\CliInput::class);
    if ($input->isLegacyInput()) {
        $input = new \App\Cron\Console\Input\CliInput($input->getMutatedLegacyInput());
    }
    $output = new Symfony\Component\Console\Output\ConsoleOutput();
// }
if ($input->hasParameterOption("defaults")) {
    $application->add(new \App\Cron\Console\Command\RegisterDefaultsCommand());
}
define("INCRONRUN", true);
define("IN_CRON", true);
$exitCode = $application->run($input, $output);
if ($output instanceof Symfony\Component\Console\Output\BufferedOutput) {
    // $config = config("portal");
    // if (isset($config['config']['display_errors']) && !empty($config['config']["display_errors"])) {
    //     echo nl2br($output->fetch());
    // }
}
exit($exitCode);

