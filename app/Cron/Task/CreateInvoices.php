<?php

namespace App\Cron\Task;

class CreateInvoices extends \App\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1520;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Generate Invoices";
    protected $defaultName = "Invoices";
    protected $systemName = "CreateInvoices";
    protected $outputs = array("invoice.created" => array("defaultValue" => 0, "identifier" => "invoice.created", "name" => "Total Invoices"));
    protected $icon = "far fa-file-alt";
    protected $successCountIdentifier = "invoice.created";
    protected $failedCountIdentifier = "";
    protected $successKeyword = "Generated";
    public function __invoke()
    {
        \App\Helpers\ProcessInvoices::createInvoices("", "", "", "", $this);
        return $this;
    }
}

?>