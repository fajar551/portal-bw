<?php
/*
 * @ PHP 5.6
 * @ Decoder version : 1.0.0.1
 * @ Release on : 24.03.2018
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Cron\Task;

class InvoiceReminders extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1550;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Generate daily reminders for unpaid and overdue invoice";
    protected $defaultName = "Invoice & Overdue Reminders";
    protected $systemName = "InvoiceReminders";
    protected $outputs = array("unpaid" => array("defaultValue" => 0, "identifier" => "unpaid", "name" => "Unpaid Reminders"), "overdue.first" => array("defaultValue" => 0, "identifier" => "overdue.first", "name" => "First Overdue Notices"), "overdue.second" => array("defaultValue" => 0, "identifier" => "overdue.second", "name" => "Second Overdue Notices"), "overdue.third" => array("defaultValue" => 0, "identifier" => "overdue.third", "name" => "Third Overdue Notices"));
    protected $icon = "far fa-envelope";
    protected $successCountIdentifier = array("unpaid", "overdue.first", "overdue.second", "overdue.third");
    protected $successKeyword = "Sent";

    // public function build()
    // {
    //     return $this->view('invoice.reminder')
    //                 ->subject('Invoice Payment Reminder');
    // }
    
    public function __invoke()
    {
        if (\WHMCS\Config\Setting::getValue("SendReminder") == "on" && \WHMCS\Config\Setting::getValue("SendInvoiceReminderDays")) {
            $this->sendUnpaidInvoiceReminders();
        }
        $this->sendOverdueInvoiceReminders();
        return true;

        $this->sendUpcomingDueReminders();
        return true;
    }
    
    public function sendUnpaidInvoiceReminders()
    {
        $invoiceids = array();
        $invoicedateyear = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + \WHMCS\Config\Setting::getValue("SendInvoiceReminderDays"), date("Y")));
        $query = "SELECT * FROM tblinvoices" . " WHERE duedate='" . $invoicedateyear . "'" . " AND `status`='Unpaid'";
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            $id = $data["id"];
            sendMessage("Invoice Payment Reminder", $id);
            run_hook("InvoicePaymentReminder", array("invoiceid" => $id, "type" => "reminder"));
            $invoiceids[] = $id;
        }
        $this->output("unpaid")->write(count($invoiceids));
        return $this;
    }
    public function sendOverdueInvoiceReminders()
    {
        $notices = array("first" => 0, "second" => 0, "third" => 0);
        $types = array("First", "Second", "Third");
        foreach ($types as $type) {
            if (\WHMCS\Config\Setting::getValue("Send" . $type . "OverdueInvoiceReminder") != "0") {
                $adddate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - (int) \WHMCS\Config\Setting::getValue("Send" . $type . "OverdueInvoiceReminder"), date("Y")));
                $result = select_query("tblinvoices,tblclients", "tblinvoices.id,tblinvoices.userid,tblclients.firstname,tblclients.lastname", array("tblinvoices.duedate" => $adddate, "tblinvoices.status" => "Unpaid", "tblclients.overideduenotices" => "0", "tblclients.id" => array("sqltype" => "TABLEJOIN", "value" => "tblinvoices.userid")));
                while ($data = mysql_fetch_array($result)) {
                    $invoiceid = $data["id"];
                    $firstname = $data["firstname"];
                    $lastname = $data["lastname"];
                    $result2 = full_query("SELECT COUNT(tblinvoiceitems.id) FROM tblinvoiceitems" . " INNER JOIN tblhosting ON tblhosting.id = tblinvoiceitems.relid" . " WHERE tblinvoiceitems.type = 'Hosting' " . " AND tblhosting.overideautosuspend = '1'" . " AND tblhosting.overidesuspenduntil > '" . date("Y-m-d") . "'" . " AND tblhosting.overidesuspenduntil != '0000-00-00' " . " AND tblinvoiceitems.invoiceid = " . (int) $invoiceid);
                    $data2 = mysql_fetch_array($result2);
                    $numoverideautosuspend = $data2[0];
                    $typeKey = strtolower($type);
                    if ($numoverideautosuspend == "0") {
                        sendMessage($type . " Invoice Overdue Notice", $invoiceid);
                        run_hook("InvoicePaymentReminder", array("invoiceid" => $invoiceid, "type" => $typeKey . "overdue"));
                        $notices[$typeKey] = $notices[$typeKey] + 1;
                    }
                }
            }
        }
        foreach ($notices as $typeKey => $value) {
            $this->output("overdue." . $typeKey)->write($value);
        }
        return $this;
    }

    // public function sendUpcomingDueReminders()
    // {
    //     // Set tanggal 2 hari dari sekarang
    //     $twoDaysFromNow = Carbon::now()->addDays(2)->format('Y-m-d');
        
    //     // Query untuk mendapatkan invoice yang akan jatuh tempo dalam 2 hari
    //     $query = "SELECT i.id, i.duedate, i.total, c.firstname, c.lastname, c.email 
    //               FROM tblinvoices i
    //               JOIN tblclients c ON c.id = i.userid
    //               WHERE i.duedate = ? 
    //               AND i.status = 'Unpaid'";
                  
    //     $result = full_query($query, [$twoDaysFromNow]);
        
    //     $sentCount = 0;
        
    //     // Loop melalui setiap invoice yang ditemukan
    //     while ($data = mysql_fetch_array($result)) {
    //         $invoiceId = $data['id'];
    //         $clientEmail = $data['email'];
    //         $clientName = $data['firstname'] . ' ' . $data['lastname'];
    //         $dueDate = $data['duedate'];
    //         $amount = $data['total'];
            
    //         // Kirim Email
    //         try {
    //             // Menggunakan fungsi sendMessage untuk mengirim email
    //             sendMessage("Invoice Payment Reminder", $invoiceId);
                
    //             // Log aktivitas pengiriman email
    //             logActivity("Email reminder sent for Invoice #$invoiceId to $clientEmail");
    //             $sentCount++;
    //         } catch (\Exception $e) {
    //             // Log jika terjadi error saat pengiriman email
    //             logActivity("Failed to send email reminder for Invoice #$invoiceId: " . $e->getMessage());
    //         }
    //     }

    //     // Tulis jumlah email yang berhasil dikirim ke output
    //     $this->output('reminders_sent')->write($sentCount);
        
    //     return $this;
    // }
}

?>