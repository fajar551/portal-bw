<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Schedule
{
	
	public function __construct()
	{
		
	}

    public static function boot(){
        (new \App\Http\Middleware\GlobalVariableLoader)->setGlobalVariableConfig();
        (new \App\Http\Middleware\GlobalVariableLoader)->setGlobalVariableLanguageClient();
        (new \App\Http\Middleware\GlobalVariableLoader)->setGlobalVariableCurrency();
    }

    public static function log($name)
    {
        $date = Carbon::now()->toDateTimeString();
        \Log::info("$name run at $date");
    }

    public static function CurrencyUpdateProductPricing()
    {
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\CurrencyUpdateProductPricing::run();
    }

	public static function CurrencyUpdateExchangeRates(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\CurrencyUpdateExchangeRates::run();
    }


    public static function CreateInvoices(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\ProcessInvoices::createInvoices();
        //\App\Helpers\HelperApi::post('AddInvoicePayment', $postData);
    }

    public static function AddLateFees(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\AddLateFees::run();
    }

    public static function InvoiceReminders(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\InvoiceReminders::run();
    }

    public static function DomainRenewalNotices(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\DomainRenewalNotices::run();
    }

    public static function DomainStatusSync(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\DomainStatusSync::run();
    }
    public static function DomainTransferSync(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\DomainTransferSync::run();
    }

    public static function CancellationRequests(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\CancellationRequests::run();
    }

    public static function AutoSuspensions(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\AutoSuspensions::run();
    }

    public static function AutoTerminations(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\AutoTerminations::run();
    }

    public static function FixedTermTerminations(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\FixedTermTerminations::run();
    }

    public static function CloseInactiveTickets(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\CloseInactiveTickets::run();
    }

    public static function EmailCampaigns(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\EmailCampaigns::run();
    }

    public static function AutoClientStatusSync(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\AutoClientStatusSync::run();
    }

    public static function TicketEscalations(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\TicketEscalations::run();
    }
    public static function DatabaseBackup(){
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\DatabaseBackup::run();
    }

    public static function OverageBilling()
    {
        Schedule::log(__METHOD__);
        Schedule::boot();
        \App\Helpers\Schedule\OverageBilling::run();
    }

}
