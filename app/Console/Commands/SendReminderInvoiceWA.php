<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Addons\SendInvoiceWa\Http\Controllers\SendInvoiceWaController;

class SendReminderInvoiceWA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminder-invoice-wa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder invoices via WhatsApp';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(SendInvoiceWaController::class)->sendReminderInvoiceWA();
        return 0;
    }
}