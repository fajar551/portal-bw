<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Modules\Addons\SendInvoiceWa\Http\Controllers\SendInvoiceWaController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\SendInvoiceReminders::class,
        \App\Console\Commands\SendReminderInvoiceWA::class,


    ];

    const task =['EmailCampaigns','AutoClientStatusSync','TicketEscalations','DatabaseBackup'];

    protected function scheduleTimezone()
    {
        return 'Asia/Jakarta';
    }


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('Automation --do=CreateInvoices')->dailyAt('6:30');
        $schedule->command('Automation --do=InvoiceReminders')->dailyAt('6:30');

        $schedule->command('send:reminder-invoice-wa')->dailyAt('08:10');
        if (env('APP_ENV') != 'production') {
            \Log::debug("schedule run");
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/Crons');

        require base_path('routes/console.php');
    }

    public function command_exists($name)
    {
        return Arr::exists(\Artisan::all(), $name);
    }
}