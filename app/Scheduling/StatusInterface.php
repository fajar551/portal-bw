<?php

namespace App\Scheduling;

interface StatusInterface
{
    public function isInProgress();
    public function isDueNow();
    public function calculateAndSetNextDue();
    public function setNextDue(\App\Helpers\Carbon $nextDue);
    public function setInProgress($state);
    public function getLastRuntime();
    public function setLastRuntime(\App\Helpers\Carbon $date);
    public function getNextDue();
}

?>