<?php

namespace App\Scheduling\Task;

class Status extends \App\Models\AbstractModel implements \App\Scheduling\StatusInterface
{
    protected $table = "tbltask_status";
    protected $dates = array("next_due", "last_run");
    protected $frequency = 1440;
    protected $fillable = array("task_id");
    public function createTable($drop = false)
    {
        // $schemaBuilder = \WHMCS\Database\Capsule::schema();
        // if ($drop) {
        //     $schemaBuilder->dropIfExists($this->getTable());
        // }
        // if (!$schemaBuilder->hasTable($this->getTable())) {
        //     $schemaBuilder->create($this->getTable(), function ($table) {
        //         $table->increments("id");
        //         $table->integer("task_id")->unsigned();
        //         $table->tinyInteger("in_progress")->default(0);
        //         $table->timestamp("last_run")->default("0000-00-00 00:00:00");
        //         $table->timestamp("next_due")->default("0000-00-00 00:00:00");
        //         $table->timestamp("created_at")->default("0000-00-00 00:00:00");
        //         $table->timestamp("updated_at")->default("0000-00-00 00:00:00");
        //     });
        // }
    }
    public function isInProgress()
    {
        if ($this->in_progress && $this->isLongOverDue()) {
            $this->in_progress = 0;
            $this->next_due = $this->advanceStale($this->getNextDue());
            $this->save();
        }
        return (bool) $this->in_progress;
    }
    public function advanceStale(\App\Helpers\Carbon $stale)
    {
        $now = \App\Helpers\Carbon::now()->second("00");
        $staleNextDue = $stale->copy();
        $i = 0;
        while ($i < 31) {
            $reasonableNextDue = $this->task->anticipatedNextRun($staleNextDue);
            if ($reasonableNextDue->isPast()) {
                if ($this->task->anticipatedNextRun($reasonableNextDue)->isPast()) {
                    $i++;
                    $staleNextDue = $reasonableNextDue;
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        if ($i == 0 || $i == 31) {
            return $now;
        }
        return $staleNextDue;
    }
    public function isLongOverDue()
    {
        return $this->getNextDue()->isPast() && 60 * 24 + 1 <= \App\Helpers\Carbon::now()->second("00")->diffInMinutes($this->getNextDue());
    }
    public function isDueNow()
    {
        return !\App\Helpers\Carbon::now()->lt($this->getNextDue()->second(0));
    }
    public function calculateAndSetNextDue()
    {
        $this->setNextDue($this->task->anticipatedNextRun());
        $this->save();
        return $this;
    }
    public function setNextDue(\App\Helpers\Carbon $nextDue)
    {
        $this->next_due = $nextDue;
        return $this;
    }
    public function setInProgress($state)
    {
        $this->in_progress = (bool) $state;
        $this->save();
        return $this;
    }
    public function getLastRuntime()
    {
        return $this->last_run;
    }
    public function setLastRuntime(\App\Helpers\Carbon $date)
    {
        $this->last_run = $date;
        $this->save();
        return $this;
    }
    public function getNextDue()
    {
        return $this->next_due;
    }
    public function getFrequency()
    {
        return $this->task->getFrequencyMinutes();
    }
    public function task()
    {
        if ($this->task_id) {
            $className = AbstractTask::where("id", $this->task_id)->value("class_name");
            return $this->belongsTo($className, "task_id");
        }
        return $this->belongsTo("App\\Scheduling\\Task\\AbstractTask", "task_id");
    }
}

?>