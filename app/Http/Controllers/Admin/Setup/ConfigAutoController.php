<?php

namespace App\Http\Controllers\Admin\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigAutoController extends Controller
{
    //
    public function index(Request $request)
    {
        global $CONFIG;
        define("ADMINAREA", true);
        $aInt = new \App\Helpers\Admin("Configure Automation Settings");
        // $aInt->title = $aInt->lang("automation", "title");
        // $aInt->sidebar = "config";
        // $aInt->icon = "autosettings";
        // $aInt->helplink = "Automation Settings";
        // $aInt->requireAuthConfirmation();
        $sub = $request->input("sub");
        if ($sub == "save") {
            $changes = array();
            $currentConfig = \App\Models\Setting::allAsArray();
            $booleanKeys = array("DomainSyncEnabled", "DomainSyncNotifyOnly", "DRAutoDeleteInactiveClients");
            $friendlyNames = array("DRAutoDeleteInactiveClients" => "Data Retention Delete Inactive Clients", "DRAutoDeleteInactiveClientsMonths" => "Data Retention Delete Inactive Clients Months");
            $changeOfDailyCronHour = false;
            $cronStatus = new \App\Cron\Status();
            $requestedDailyCronHour = (int) $request->input("dailycronexecutionhour");
            $currentDailyCronHour = $cronStatus->getDailyCronExecutionHour();
            if ($requestedDailyCronHour !== (int) $currentDailyCronHour->format("H")) {
                $cronStatus->setDailyCronExecutionHour($requestedDailyCronHour);
                foreach (\App\Scheduling\Task\AbstractTask::all() as $task) {
                    $status = $task->getStatus();
                    $currentNextDue = $status->getNextDue();
                    $currentNextDue->hour($requestedDailyCronHour)->second("00");
                    if ($currentNextDue->isPast()) {
                        $newNextDue = $task->anticipatedNextRun($currentNextDue);
                    } else {
                        $newNextDue = $currentNextDue;
                    }
                    $status->setNextDue($newNextDue)->save();
                }
                $changeOfDailyCronHour = true;
            }
            $settingsToSave = array("DRAutoDeleteInactiveClients" => $request->input("autodeleteinactiveclients"), "DRAutoDeleteInactiveClientsMonths" => $request->input("autodeleteinactiveclientsmonths"), "DomainSyncEnabled" => $request->input("domainsyncenabled"), "DomainSyncNextDueDate" => $request->input("domainsyncnextduedate"), "DomainSyncNextDueDateDays" => (int) $request->input("domainsyncnextduedatedays"), "DomainSyncNotifyOnly" => $request->input("domainsyncnotifyonly"), "DomainStatusSyncFrequency" => (int) $request->input("domain_status_sync_frequency"), "DomainTransferStatusCheckFrequency" => (int) $request->input("domain_transfer_sync_frequency"));
            if ($settingsToSave["DomainStatusSyncFrequency"] < 0) {
                $settingsToSave["DomainStatusSyncFrequency"] = 0;
            }
            if ($settingsToSave["DomainTransferStatusCheckFrequency"] < 0) {
                $settingsToSave["DomainTransferStatusCheckFrequency"] = 0;
            }
            foreach ($settingsToSave as $key => $value) {
                if ($currentConfig[$key] != $value) {
                    if (in_array($key, $friendlyNames)) {
                        $friendlySetting = $friendlyNames[$key];
                    } else {
                        $regEx = "/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x";
                        $friendlySettingParts = preg_split($regEx, $key);
                        $friendlySetting = implode(" ", $friendlySettingParts);
                    }
                    $currentValue = $currentConfig[$key];
                    $newValue = $value;
                    if (in_array($key, $booleanKeys)) {
                        $currentValue = "off";
                        $newValue = "on";
                        if (!$value || $value === false || $value == "off") {
                            $currentValue = "on";
                            $newValue = "off";
                        }
                    }
                    $changes[] = (string) $friendlySetting . " changed from '" . $currentValue . "' to '" . $newValue . "'";
                }
                \App\Helpers\Cfg::setValue($key, $value);
            }
            \App\Helpers\Cfg::setValue("AutoSuspension", $request->input("autosuspend"));
            \App\Helpers\Cfg::setValue("AutoSuspensionDays", $request->input("days"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBefore", $request->input("createinvoicedays"));
            \App\Helpers\Cfg::setValue("CreateDomainInvoiceDaysBefore", $request->input("createdomaininvoicedays"));
            \App\Helpers\Cfg::setValue("SendReminder", $request->input("invoicesendreminder"));
            \App\Helpers\Cfg::setValue("SendInvoiceReminderDays", $request->input("invoicesendreminderdays"));
            \App\Helpers\Cfg::setValue("UpdateStatsAuto", $request->input("updatestatusauto"));
            \App\Helpers\Cfg::setValue("CloseInactiveTickets", $request->input("closeinactivetickets"));
            \App\Helpers\Cfg::setValue("PruneTicketAttachmentsMonths", (int) $request->input("remove_inactive_attachments"));
            \App\Helpers\Cfg::setValue("AutoTermination", $request->input("autotermination"));
            \App\Helpers\Cfg::setValue("AutoTerminationDays", $request->input("autoterminationdays"));
            \App\Helpers\Cfg::setValue("AutoUnsuspend", $request->input("autounsuspend"));
            \App\Helpers\Cfg::setValue("AddLateFeeDays", $request->input("addlatefeedays"));
            \App\Helpers\Cfg::setValue("SendFirstOverdueInvoiceReminder", $request->input("invoicefirstoverduereminder"));
            \App\Helpers\Cfg::setValue("SendSecondOverdueInvoiceReminder", $request->input("invoicesecondoverduereminder"));
            \App\Helpers\Cfg::setValue("SendThirdOverdueInvoiceReminder", $request->input("invoicethirdoverduereminder"));
            \App\Helpers\Cfg::setValue("AutoCancellationRequests", $request->input("autocancellationrequests"));
            \App\Helpers\Cfg::setValue("CCProcessDaysBefore", $request->input("ccprocessdaysbefore"));
            \App\Helpers\Cfg::setValue("CCAttemptOnlyOnce", $request->input("ccattemptonlyonce"));
            \App\Helpers\Cfg::setValue("CCRetryEveryWeekFor", $request->input("ccretryeveryweekfor"));
            \App\Helpers\Cfg::setValue("CCDaySendExpiryNotices", $request->input("ccdaysendexpirynotices"));
            \App\Helpers\Cfg::setValue("CCDoNotRemoveOnExpiry", $request->input("donotremovecconexpiry"));
            \App\Helpers\Cfg::setValue("CurrencyAutoUpdateExchangeRates", $request->input("currencyautoupdateexchangerates"));
            \App\Helpers\Cfg::setValue("CurrencyAutoUpdateProductPrices", $request->input("currencyautoupdateproductprices"));
            \App\Helpers\Cfg::setValue("OverageBillingMethod", $request->input("overagebillingmethod"));
            \App\Helpers\Cfg::setValue("ReversalChangeInvoiceStatus", $request->input("revchangeinvoicestatus"));
            \App\Helpers\Cfg::setValue("ReversalChangeDueDates", $request->input("revchangeduedates"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeMonthly", $request->input("invoicegenmonthly"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeQuarterly", $request->input("invoicegenquarterly"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeSemiAnnually", $request->input("invoicegensemiannually"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeAnnually", $request->input("invoicegenannually"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeBiennially", $request->input("invoicegenbiennially"));
            \App\Helpers\Cfg::setValue("CreateInvoiceDaysBeforeTriennially", $request->input("invoicegentriennially"));
            \App\Helpers\Cfg::setValue("AutoClientStatusChange", $request->input("autoclientstatuschange"));
            $renewals = $request->input('renewals') ?? [];
            foreach ($renewals as $count => $renewal) {
                if ($request->input("renewalWhen.". (int) $count) == "after" && 0 < $renewal) {
                    $renewals[$count] *= -1;
                }
            }
            \App\Helpers\Cfg::setValue("DomainRenewalNotices", implode(",", $renewals));
            $savedConfig = \App\Models\Setting::allAsArray();
            foreach ($currentConfig as $setting => $value) {
                if ($setting == "DomainRenewalNotices") {
                    $options = array("First", "Second", "Third", "Fourth", "Fifth");
                    $currentSetting = explode(",", $value);
                    foreach ($currentSetting as $key => $renewal) {
                        if ($renewals[$key] != $renewal) {
                            $currentBeforeAfter = $newBeforeAfter = "";
                            if (0 < $renewal) {
                                $currentBeforeAfter = " before ";
                            } else {
                                if ($renewal < 0) {
                                    $renewal *= -1;
                                    $currentBeforeAfter = " after ";
                                }
                            }
                            if (0 < $renewals[$key]) {
                                $newBeforeAfter = " before";
                            } else {
                                if ($renewals[$key] < 0) {
                                    $renewals[$key] *= -1;
                                    $newBeforeAfter = " after";
                                }
                            }
                            $changes[] = (string) $options[$key] . " Domain Renewal Notice changed from " . (string) $renewal . " day(s)" . $currentBeforeAfter . "to " . (string) $renewals[$key] . " day(s)" . $newBeforeAfter;
                        }
                    }
                } else {
                    if (in_array($setting, $settingsToSave)) {
                        continue;
                    }
                    if ($savedConfig[$setting] != $value) {
                        if ($value == "on" && !$savedConfig[$setting]) {
                            $savedConfig[$setting] = "off";
                        }
                        if ($savedConfig[$setting] == "on" && !$value) {
                            $value = "off";
                        }
                        $regEx = "/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/x";
                        $friendlySettingParts = preg_split($regEx, $setting);
                        $friendlySetting = implode(" ", $friendlySettingParts);
                        $changes[] = (string) $friendlySetting . " changed from '" . $value . "' to '" . $savedConfig[$setting] . "'";
                    }
                }
            }
            $autoSuspendEmail = $request->input("autoSuspendEmail");
            $disableSuspendEmail = $autoSuspendEmail ? "0" : "1";
            $autoUnsuspendEmail = $request->input("autoUnsuspendEmail");
            $disableUnsuspendEmail = $autoUnsuspendEmail ? "0" : "1";
            $template = \App\Models\Emailtemplate::where("type", "=", "product")->where("name", "=", "Service Suspension Notification")->get()->first();
            if (!is_null($template)) {
                if ($template->disabled != $disableSuspendEmail) {
                    $changes[] = "Service Suspension Notification email template " . ($disableSuspendEmail == "0" ? "Enabled" : "Disabled");
                }
                $template->disabled = $disableSuspendEmail;
                $template->save();
            }
            $template = \App\Models\Emailtemplate::where("type", "=", "product")->where("name", "=", "Service Unsuspension Notification")->get()->first();
            if (!is_null($template)) {
                if ($template->disabled != $disableUnsuspendEmail) {
                    $changes[] = "Service Unsuspension Notification email template " . ($disableUnsuspendEmail == "0" ? "Enabled" : "Disabled");
                }
                $template->disabled = $disableUnsuspendEmail;
                $template->save();
            }
            if ($changes) {
                \App\Helpers\AdminFunctions::logAdminActivity("Automation Settings Changed. Changes made: " . implode(". ", $changes));
            }
            // redir("success=1" . ($changeOfDailyCronHour ? "&cronhourchanged=1" : ""));
            return redirect()->route('admin.configauto', ['success' => '1', "cronhourchanged" => $changeOfDailyCronHour ? "1" : ""]);
        }
        $cron = new \App\Helpers\Cron();
        if ($lastInvocationTime = $cron->getLastCronInvocationTime()) {
            $lastInvocationTime = (new \App\Helpers\Functions)->fromMySQLDate($lastInvocationTime->format("Y-m-d H:i:s"), true);
        } else {
            $lastInvocationTime = "Never";
        }
        $result = \App\Models\Configuration::all();
        foreach ($result->toArray() as $data) {
            $setting = $data["setting"];
            $value = $data["value"];
            $CONFIG[(string) $setting] = (string) $value;
        }
        $autoUnsuspendEmail = \App\Models\Emailtemplate::where("type", "=", "product")->where("name", "=", "Service Unsuspension Notification")->get()->first();
        $autoUnsuspendChecked = "";
        if (!is_null($autoUnsuspendEmail) && !$autoUnsuspendEmail->disabled) {
            $autoUnsuspendChecked = " checked";
        }
        $autoSuspendEmail = \App\Models\Emailtemplate::where("type", "=", "product")->where("name", "=", "Service Suspension Notification")->get()->first();
        $autoSuspendChecked = "";
        if (!is_null($autoSuspendEmail) && !$autoSuspendEmail->disabled) {
            $autoSuspendChecked = " checked";
        }
        $jscode = "function showadvinvoice() {\n    \$(\"#advinvoicesettings\").slideToggle();\n}";
        
        return view('pages.setup.automationsettings.index', [
            'aInt' => $aInt,
            'cron' => $cron,
            'jscode' => $jscode,
            'autoUnsuspendChecked' => $autoUnsuspendChecked,
            'autoSuspendChecked' => $autoSuspendChecked,
            'lastInvocationTime' => $lastInvocationTime,
            'artisan_path' => base_path('artisan'),
        ]);
    }
}
