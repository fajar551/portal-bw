<?php

namespace App\Cron;

class Status
{
    public function setLastDailyCronInvocationTime(\App\Helpers\Carbon $datetime = NULL)
    {
        if (!$datetime instanceof \App\Helpers\Carbon) {
            $datetime = \App\Helpers\Carbon::now();
        }
        \App\Helpers\Cfg::setValue("lastDailyCronInvocationTime", $datetime->toDateTimeString());
    }
    public function getLastDailyCronInvocationTime()
    {
        $datetime = null;
        $lastDailyTime = \App\Helpers\Cfg::getValue("lastDailyCronInvocationTime");
        if (!empty($lastDailyTime)) {
            try {
                $datetime = new \App\Helpers\Carbon($lastDailyTime);
            } catch (\Exception $e) {
            }
        }
        return $datetime;
    }
    public function hasDailyCronRunInLast24Hours()
    {
        return $this->hasDailyCronRunSince(24);
    }
    public function hasDailyCronRunSince($hours)
    {
        $lastCronInvocationTime = $this->getLastDailyCronInvocationTime();
        if (!empty($lastCronInvocationTime)) {
            $lastCronInvocationTime = new \App\Helpers\Carbon($lastCronInvocationTime);
            $minTime = \App\Helpers\Carbon::now()->subHours((int) $hours);
            if ($lastCronInvocationTime->gt($minTime)) {
                return true;
            }
        }
        return false;
    }
    public function hasDailyCronEverRun()
    {
        $lastCronInvocationTime = $this->getLastDailyCronInvocationTime();
        return !empty($lastCronInvocationTime);
    }
    public function hasCronEverBeenInvoked()
    {
        return $this->getLastCronInvocationTime();
    }
    public static function getDailyCronExecutionHour()
    {
        $hour = \App\Helpers\Cfg::getValue("DailyCronExecutionHour");
        $datetime = new \App\Helpers\Carbon("January 2, 1970 00:00:00");
        if (!$hour) {
            $datetime->hour("09");
        } else {
            $datetime->hour($hour);
        }
        return $datetime;
    }
    public static function setDailyCronExecutionHour($time = "09")
    {
        try {
            if (is_numeric($time)) {
                $time = (string) $time;
                if (strlen($time) != 2) {
                    $time = "0" . $time;
                }
                $time .= ":00:00";
            }
            $datetime = new \App\Helpers\Carbon("January 2, 1970 " . $time);
        } catch (\Exception $e) {
            $datetime = new \App\Helpers\Carbon("January 2, 1970 09:00:00");
        }
        \App\Helpers\Cfg::setValue("DailyCronExecutionHour", $datetime->format("H"));
    }
    public function isOkayToRunDailyCronNow()
    {
        $lastDailyRunTime = $this->getLastDailyCronInvocationTime();
        $now = \App\Helpers\Carbon::now();
        $dailyCronHourWindowStart = self::getDailyCronExecutionHour();
        if ($now->format("H") == $dailyCronHourWindowStart->format("H")) {
            if (!$lastDailyRunTime) {
                return true;
            }
            if (!$now->isSameDay($lastDailyRunTime)) {
                return true;
            }
        }
        return false;
    }
    public function hasCronBeenInvokedIn24Hours()
    {
        if ($this->hasDailyCronRunInLast24Hours()) {
            return true;
        }
        $invokeTime = $this->getLastCronInvocationTime();
        if (!empty($invokeTime)) {
            $now = \App\Helpers\Carbon::now();
            $minimumDateTimeForNextInvocation = $invokeTime->addDay()->second(0)->subMinute();
            if ($now->lt($minimumDateTimeForNextInvocation)) {
                return true;
            }
        }
        return false;
    }
    public function getLastCronInvocationTime()
    {
        $transientData = \App\Helpers\TransientData::getInstance();
        $anyInvocation = $transientData->retrieve("lastCronInvocationTime");
        if ($anyInvocation) {
            try {
                return new \App\Helpers\Carbon($anyInvocation);
            } catch (\Exception $e) {
                return null;
            }
        }
        return $this->getLastDailyCronInvocationTime();
    }
    public function setCronInvocationTime()
    {
        $now = \App\Helpers\Carbon::now();
        \App\Helpers\TransientData::getInstance()->store("lastCronInvocationTime", $now->toDateTimeString(), 48 * 60 * 60);
    }
}

?>