<?php

namespace App\Cron\Console\Helper;

class DailyCronHelper implements \Symfony\Component\Console\Helper\HelperInterface
{
    protected $helperSet = NULL;
    protected $report = NULL;
    protected $io = NULL;
    protected $isDailyCronInvocation = NULL;
    protected $status = NULL;
    public function __construct(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output, \App\Cron\Status $status)
    {
        $this->io = new \App\Cron\Console\Style\TaskStyle($input, $output);
        $this->status = $status;
        $this->isDailyCronInvocation = $this->calculateIfIsDailyCronInvocation();
    }
    public function setHelperSet(\Symfony\Component\Console\Helper\HelperSet $helperSet = NULL)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet()
    {
        return $this->helperSet;
    }
    public function getName()
    {
        return "daily-cron";
    }
    public function calculateIfIsDailyCronInvocation()
    {
        if ($this->status->isOkayToRunDailyCronNow()) {
            return true;
        }
        if ($this->io->hasForceOption()) {
            return true;
        }
        return false;
    }
    public function isDailyCronInvocation()
    {
        return $this->isDailyCronInvocation;
    }
    public function getReport()
    {
        if (!$this->report) {
            $dailyCronHelper = $this;
            \App\Helpers\Hooks::add_hook("PostAutomationTask", 0, function ($task, $completed) use($dailyCronHelper) {
                $dailyCronHelper->hookRegisterTaskCompletion($task, $completed);
            });
            $this->report = new \App\Log\Register\DailyCronReport();
        }
        return $this->report;
    }
    public function sendDailyCronDigest()
    {
        $sendReport = true;
        $reason = "";
        if ($this->io->getInput()->hasOption("email-report") && !$this->io->getInput()->getOption("email-report")) {
            $sendReport = false;
            $reason = " per command options";
        }
        $hookResults = \App\Helpers\Hooks::run_hook("DailyCronJobPreEmail", array());
        foreach ($hookResults as $result) {
            if ($result == true) {
                $sendReport = false;
                $reason = " per result of DailyCronJobPreEmail hook";
            }
        }
        if ($this->io->isDebug()) {
            $this->io->text(sprintf("%s Daily Cron Digest email%s", $sendReport ? "Sending" : "Not sending", $reason));
        }
        if ($sendReport) {
            \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Cron Job Activity", $this->getReport()->toHtmlDigest(), 0, false);
        }
    }
    public function isDailyCronRunningOnTime()
    {
        $dailyCronHour = $this->status->getDailyCronExecutionHour();
        $dailyCronHourPassedToday = $dailyCronHour->format("H") < \App\Helpers\Carbon::now()->format("H");
        $lastDailyCronRun = $this->status->getLastDailyCronInvocationTime();
        $lastDailyCronWasRunToday = !is_null($lastDailyCronRun) ? $lastDailyCronRun->isToday() : false;
        if ($dailyCronHourPassedToday && !$lastDailyCronWasRunToday) {
            return false;
        }
        if (!$this->status->hasDailyCronRunSince(32)) {
            return false;
        }
        return true;
    }
    public function sendDailyNotificationDailyCronNotExecuting()
    {
        if (!$this->status->hasDailyCronEverRun()) {
            return $this;
        }
        $dailyCronHour = $this->status->getDailyCronExecutionHour();
        $hasBeenNotifiedToday = false;
        $lastNotification = \App\Helpers\TransientData::getInstance()->retrieve("lastNotificationDailyCronOutOfSync");
        if ($lastNotification) {
            $lastNotification = new \App\Helpers\Carbon($lastNotification);
            $hasBeenNotifiedToday = $lastNotification->isToday();
        }
        if (!$hasBeenNotifiedToday) {
            $outOfSyncCronMessage = "Your WHMCS is configured to perform the Daily System Cron during the hour of %hour%.\n However, the Daily System Cron did not execute within that hour as expected.\n<br/><br/>\nThis may be due to the scheduled time specified in your web hosting control\n panel's cron entry.  Please ensure your web hosting control panel executes the\n WHMCS System Cron (cron.php) at least once during the hour of %hour%.\n<br/><br/>\nIf you have confirmed that setting, and you continue to receive this message,\n then please refer to the <a href=\"https://docs.whmcs.com/Crons\" target=\"_blank\">\nWHMCS Cron documentation</a> to ensure you have itemized the appropriate command\n and any additional options.\n<br/><br/>\nPlease contact <a href=\"https://whmcs.com/support\" target=\"_blank\">WHMCS Support\n</a> if you require further assistance.";
            $outOfSyncCronMessage = str_replace("%hour%", $dailyCronHour->format("g a"), $outOfSyncCronMessage);
            $outOfSyncCronMessage = str_replace("\n", "", $outOfSyncCronMessage);
            \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Daily System Cron Attention Needed", $outOfSyncCronMessage);
            \App\Helpers\TransientData::getInstance()->store("lastNotificationDailyCronOutOfSync", \App\Helpers\Carbon::now()->toDateTimeString(), 1460);
        }
        return $this;
    }
    public function hookRegisterTaskCompletion($task, $completed = true)
    {
        $report = $this->getReport();
        if ($completed) {
            $report->completed($task);
        } else {
            $report->notCompleted($task);
        }
    }
    public function startDailyCron()
    {
        $this->status->setLastDailyCronInvocationTime();
        \App\Helpers\LogActivity::Save("Cron Job: Starting Daily Automation Tasks");
        \App\Helpers\TransientData::getInstance()->delete("cronComplete");
        $this->getReport()->start();
        \App\Helpers\Hooks::run_hook("PreCronJob", array());
    }
    public function endDailyCron()
    {
        \App\Helpers\Vat::resetNumbers();
        \App\Helpers\TransientData::getInstance()->store("cronComplete", "true", 86400);
        \App\Helpers\LogActivity::Save("Cron Job: Completed Daily Automation Tasks");
        $this->getReport()->finish();
        $this->sendDailyCronDigest();
        \App\Helpers\Hooks::run_hook("DailyCronJob", array());
    }
}

?>