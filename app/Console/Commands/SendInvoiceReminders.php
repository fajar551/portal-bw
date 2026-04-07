<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Modules\Addons\SendInvoiceWa\Http\Controllers\SendInvoiceWaController;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Kirim pengingat invoice yang akan jatuh tempo dalam 2 hari';

    public function handle()
    {
        app(SendInvoiceWaController::class)->sendReminderInvoiceWA();

        $twoDaysFromNow = Carbon::now()->addDays(2)->format('Y-m-d');

        $invoices = DB::table('tblinvoices as i')
            ->join('tblclients as c', 'c.id', '=', 'i.userid')
            ->where('i.duedate', $twoDaysFromNow)
            ->where('i.status', 'Unpaid')
            ->select(
                'i.id as invoice_id',
                'i.invoicenum',
                'i.date as invoice_date_created',
                'i.duedate as invoice_date_due',
                'i.total as invoice_total',
                'i.paymentmethod as invoice_payment_method',
                'c.email',
                'c.firstname',
                'c.lastname',
                'c.id as client_id'
            )
            ->get();

        $this->info("Found {$invoices->count()} invoices due in 2 days");

        // Ambil template email dari database
        $template = DB::table('tblemailtemplates')
            ->where('name', 'Invoice Payment Reminder')
            ->first();

        foreach ($invoices as $invoice) {
            try {
                // Tentukan periode tagihan
                $tanggalMulai = Carbon::parse($invoice->invoice_date_created)->format('d-m-Y');
                $tanggalJatuhTempo = Carbon::parse($invoice->invoice_date_due)->format('d-m-Y');
                $period = "{$tanggalMulai} - {$tanggalJatuhTempo}";

                // Gantikan placeholder dengan data aktual
                $invoiceLink = route('pages.services.mydomains.viewinvoiceweb', ['id' => $invoice->invoice_id]);

                $message = str_replace(
                    ['{$client_name}', '{$invoice_num}', '{$invoice_date_created}', '{$invoice_date_due}', '{$invoice_payment_method}', '{$invoice_total}', '{$invoice_link}', '{$signature}', '{$client_id}', '{$period}'],
                    ["{$invoice->firstname} {$invoice->lastname}", $invoice->invoicenum, $invoice->invoice_date_created, $invoice->invoice_date_due, $invoice->invoice_payment_method, $invoice->invoice_total, $invoiceLink, 'Your Signature', $invoice->client_id, $period],
                    $template->message
                );

                // Kirim email
                Mail::send([], [], function ($mail) use ($invoice, $template, $message) {
                    $subject = str_replace('{$invoice_id}', $invoice->invoice_id, $template->subject);
                    $mail->to($invoice->email)
                         ->subject($subject)
                         ->setBody($message, 'text/html'); // Pastikan format HTML
                });

                $this->info("Email sent for invoice #{$invoice->invoice_id} to {$invoice->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send email for invoice #{$invoice->invoice_id}: {$e->getMessage()}");
            }
        }
    }

//     public function handle()
//     {
//         app(SendInvoiceWaController::class)->sendReminderInvoiceWA();

//         // Mengambil semua invoice yang jatuh tempo pada tanggal 1 Januari 2025 dan belum dibayar
//         $invoices = DB::table('tblinvoices as i')
//             ->join('tblclients as c', 'c.id', '=', 'i.userid')
//             ->where('i.duedate', '2025-01-01') // Mengubah tanggal jatuh tempo menjadi 1 Januari 2025
//             ->where('i.status', 'Unpaid')
//             ->select(
//                 'i.id as invoice_id',
//                 'i.invoicenum',
//                 'i.date as invoice_date_created',
//                 'i.duedate as invoice_date_due',
//                 'i.total as invoice_total',
//                 'i.paymentmethod as invoice_payment_method',
//                 'c.email',
//                 'c.firstname',
//                 'c.lastname',
//                 'c.id as client_id'
//             )
//             ->get();

//         $this->info("Found {$invoices->count()} invoices due on 1 January 2025");

//         // Ambil template email dari database
//         $template = DB::table('tblemailtemplates')
//             ->where('name', 'Invoice Payment Reminder')
//             ->first();

//         foreach ($invoices as $invoice) {
//             try {
//                 // Tentukan periode tagihan
//                 $tanggalMulai = Carbon::parse($invoice->invoice_date_created)->format('d-m-Y');
//                 $tanggalJatuhTempo = Carbon::parse($invoice->invoice_date_due)->format('d-m-Y');
//                 $period = "{$tanggalMulai} - {$tanggalJatuhTempo}";

//                 // Gantikan placeholder dengan data aktual
//                 $invoiceLink = route('pages.services.mydomains.viewinvoiceweb', ['id' => $invoice->invoice_id]);

//                 $message = str_replace(
//                     ['{$client_name}', '{$invoice_num}', '{$invoice_date_created}', '{$invoice_date_due}', '{$invoice_payment_method}', '{$invoice_total}', '{$invoice_link}', '{$signature}', '{$client_id}', '{$period}'],
//                     ["{$invoice->firstname} {$invoice->lastname}", $invoice->invoicenum, $invoice->invoice_date_created, $invoice->invoice_date_due, $invoice->invoice_payment_method, $invoice->invoice_total, $invoiceLink, 'Your Signature', $invoice->client_id, $period],
//                     $template->message
//                 );

//                 // Kirim email
//                 Mail::send([], [], function ($mail) use ($invoice, $template, $message) {
//     $subject = str_replace('{$invoice_id}', $invoice->invoice_id, $template->subject);
//     $mail->to($invoice->email)
//          ->subject($subject)
//          ->setBody($message, 'text/html'); // Pastikan format HTML
// });

//                 $this->info("Email sent for invoice #{$invoice->invoice_id} to {$invoice->email}");
//                 // Menambahkan jeda 30 detik setelah setiap pengiriman email
//                 sleep(30);
//             } catch (\Exception $e) {
//                 $this->error("Failed to send email for invoice #{$invoice->invoice_id}: {$e->getMessage()}");
//             }
//         }
//     }
}