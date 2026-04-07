<?php

namespace App\Cron\Console\Command;

class AllCommand extends AbstractCronCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName("all")->setDescription("Execute all automation tasks")->setHelp("This command will perform all automation tasks that are " . "due to run at the time of script execution");
        $this->addOption("--force", "-F", \Symfony\Component\Console\Input\InputOption::VALUE_NONE, "Force run tasks regardless if they are due or currently being run " . "by another process");
        $this->addOption("--email-report", "", \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, "Send Daily Cron Digest email. Options are \"0\" and \"1\". Defaults to \"1\" if performing the Daily Cron routines", 1);
    }
    public function getInputBasedCollection(\Symfony\Component\Console\Input\InputInterface $input)
    {
        return $this->getHelper("task-collection")->allTasks()->isEnabled();
    }
    protected function beforeExecution()
    {
        parent::beforeExecution();
        $dailyCronHelper = new \App\Cron\Console\Helper\DailyCronHelper($this->io->getInput(), $this->io->getOutput(), new \App\Cron\Status());
        $this->getHelperSet()->set($dailyCronHelper);
        if ($dailyCronHelper->isDailyCronInvocation()) {
            $dailyCronHelper->startDailyCron();
            if ($this->io->isDebug()) {
                $this->io->text("Daily Cron Automation Mode");
            }
        }
        return $this;
    }
    protected function afterExecution()
    {
        $dailyCronHelper = $this->getHelper("daily-cron");
        if ($dailyCronHelper->isDailyCronInvocation()) {
            $dailyCronHelper->endDailyCron();
        }
        return parent::afterExecution();
    }
    protected function getSystemQueue()
    {
        if ($this->getHelper("daily-cron")->isDailyCronInvocation()) {
            $tasks = array(new \App\Cron\Task\DataNormalization((new \App\Cron\Task\DataNormalization())->getDefaultAttributes()), new \App\Cron\Task\LicenseNotice((new \App\Cron\Task\LicenseNotice())->getDefaultAttributes()), new \App\Cron\Task\SystemConfiguration((new \App\Cron\Task\SystemConfiguration())->getDefaultAttributes()), new \App\Cron\Task\DatabaseBackup((new \App\Cron\Task\DatabaseBackup())->getDefaultAttributes()));
        } else {
            $tasks = array();
        }
        return new \App\Scheduling\Task\Collection($tasks);
    }
}

?>