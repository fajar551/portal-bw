<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Automation extends Command
{
   
    const task =['CurrencyUpdateExchangeRates','CreateInvoices','AddLateFees','InvoiceReminders','DomainRenewalNotices','DomainStatusSync','DomainTransferSync','CancellationRequests','AutoSuspensions','AutoTerminations','FixedTermTerminations','CloseInactiveTickets','EmailCampaigns','AutoClientStatusSync','TicketEscalations','DatabaseBackup'];
   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automation {--skip=null}  {--do=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'automates tasks CBMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $skip = ($this->option('skip') != 'null')?explode(',',$this->option('skip')):array();
        $tasks=($this->option('do') != 'null' )?explode(',',$this->option('do')):array();
        if(empty($skip) && empty($tasks)){
            foreach(self::task as $task){
                //echo "\App\Helper\Schedule::{$task}";
                ("\App\Helpers\Schedule::{$task}")();
            }
        }else{
            $tasks=!empty($tasks)?$tasks:self::task;
            foreach($tasks as $task){
                if(!empty($tasks)){
                    if(!in_array($task,$skip)){
                        ("\App\Helpers\Schedule::{$task}")();
                    }
                }else{
                    ("\App\Helpers\Schedule::{$task}")();
                }
               
            }
        
        }


        


        //return 0;
    }
}
