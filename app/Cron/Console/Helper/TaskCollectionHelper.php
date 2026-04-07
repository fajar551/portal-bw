<?php

namespace App\Cron\Console\Helper;

class TaskCollectionHelper extends \App\Scheduling\Task\AbstractTask implements \Symfony\Component\Console\Helper\HelperInterface
{
    protected $helperSet = NULL;
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
        return "task-collection";
    }
    public function allTasks()
    {
        return $this->all();
    }
    public function getExcludeCollection(\Symfony\Component\Console\Input\InputInterface $input)
    {
        return $this->allTasks()->isEnabled()->isLevel(\App\Scheduling\Task\TaskInterface::ACCESS_USER)->filter(function ($task) use($input) {
            $optionName = $task->getSystemName();
            if ($input->hasOption($optionName) && $input->getOption($optionName)) {
                return false;
            }
            return true;
        });
    }
    public function getIncludeCollection(\Symfony\Component\Console\Input\InputInterface $input)
    {
        return $this->allTasks()->isEnabled()->isLevel(\App\Scheduling\Task\TaskInterface::ACCESS_USER)->filter(function ($task) use($input) {
            $optionName = $task->getSystemName();
            if ($input->hasOption($optionName) && $input->getOption($optionName)) {
                return true;
            }
            return false;
        });
    }
    public function addTasksAsOptions(\Symfony\Component\Console\Command\Command $cmd)
    {
        $tasks = $this->allTasks()->isLevel(\App\Scheduling\Task\TaskInterface::ACCESS_USER);
        $tasks->add(new \App\Cron\Task\DatabaseBackup((new \App\Cron\Task\DatabaseBackup())->getDefaultAttributes()));
        foreach ($tasks as $task) {
            $cmd->addOption("--" . $task->getSystemName(), "", \Symfony\Component\Console\Input\InputOption::VALUE_NONE, $task->getDescription());
        }
        return $this;
    }
    public function updateStatusPreRun(\App\Scheduling\Task\Collection $tasks, $setNextDue = true)
    {
        $tasks->each(function (\App\Scheduling\Task\TaskInterface $task) use($setNextDue) {
            $status = $task->getStatus();
            $status->setLastRuntime(\App\Helpers\Carbon::now());
            if ($setNextDue) {
                $status->calculateAndSetNextDue();
            }
            $status->setInProgress(true);
        });
        return $this;
    }
    public function filterShouldRun(\App\Scheduling\Task\Collection $tasks)
    {
        $tasks = $tasks->filter(function (\App\Scheduling\Task\TaskInterface $task) {
            return !$task->getStatus()->isInProgress();
        })->filter(function (\App\Scheduling\Task\TaskInterface $task) {
            return $task->getStatus()->isDueNow();
        });
        return $tasks;
    }
    public function filterTasksForToday(\App\Scheduling\Task\Collection $tasks)
    {
        $tasks = $tasks->filter(function (\App\Scheduling\Task\TaskInterface $model) {
            if ($model->isDailyTask() || \App\Helpers\Carbon::now()->isSameDay($model->monthlyDayOfExecution())) {
                return true;
            }
            return false;
        });
        return $tasks;
    }
    public function filterTasksForDailyCron(\App\Scheduling\Task\Collection $tasks)
    {
        $tasks = $tasks->filter(function (\App\Scheduling\Task\TaskInterface $model) {
            return !$model->isSkipDailyCron();
        });
        return $tasks;
    }
}

?>