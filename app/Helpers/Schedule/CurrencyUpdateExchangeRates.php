<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;

class CurrencyUpdateExchangeRates
{   
    const EXCHANGE_RATE_FEED_URL = "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";
    public function __construct()
	{
		
	}

    public static function run($task=null){
        $stuff=Http::post(static::EXCHANGE_RATE_FEED_URL);
        $stuff=explode("\n",$stuff->body());
        $exchrate = array();
        $exchrate["EUR"] = 1;
        foreach ($stuff as $line) {
            $line = trim($line);
            $matchstr = "currency='";
            $pos1 = strpos($line, $matchstr);
            if ($pos1) {
                $currencysymbol = substr($line, $pos1 + strlen($matchstr), 3);
                $matchstr = "rate='";
                $pos2 = strpos($line, $matchstr);
                $ratestr = substr($line, $pos2 + strlen($matchstr));
                $pos3 = strpos($ratestr, "'");
                $rate = substr($ratestr, 0, $pos3);
                $exchrate[$currencysymbol] = $rate;
            }
        }

        $responses=\App\Helpers\Hooks::run_hook('FetchCurrencyExchangeRates',$exchrate);
        foreach ($responses as $response) {
            if (is_array($response)) {
                foreach ($response as $currencyCode => $rate) {
                    $exchrate[$currencyCode] = $rate;
                }
            }
        }

        $result=\App\Models\Currency::where('default','!=',1)->first();
        
        $currencycode = $result->code;
        $baserate = isset($exchrate[$currencycode]) ? $exchrate[$currencycode] : "";
        $return = "";

        $default=\App\Models\Currency::where('default',1)->orderBy('code')->get();
        foreach($default as $data){
            $id = $data->id;
            $code = $data->code;
            $coderate = $exchrate[$code];
            $exchangerate = 0;
            if ($coderate) {
                $codeRateRatio = $baserate / $coderate;
                if ($codeRateRatio) {
                    $exchangerate = round(1 / $codeRateRatio, 5);
                }
            }
            if (0 < $exchangerate) {
                $update=\App\Models\Currency::find($id);
                $update->rate = $exchangerate;
                $update->save();
                if ($task) {
                    LogActivity::Save("Updated " . $code . " Exchange Rate to " . $exchangerate);
                }
                $return .= "Updated " . $code . " Exchange Rate to " . $exchangerate . "<br />";
            }else{
                if ($task) {
                    $updatedFailed++;
                    LogActivity::Save("Update Failed for " . $code . " Exchange Rate");
                }
                $return .= "Update Failed for " . $code . " Exchange Rate<br />";
            }
        }

        if ($task) {
            if ($updatedFailed) {
                //$task->output("updated")->write(0);
            } else {
                //$task->output("updated")->write(1);
            }
        }

        //dd($return);
        return $return;

    }



    public static function fetchCurrentRates(){
        $response = Http::post(static::EXCHANGE_RATE_FEED_URL);
        $rawFeed=explode("\n",$response->body());
        $exchangeRates = array();
        $exchangeRates["EUR"] = 1;
        foreach ($rawFeed as $line) {
            $line = trim($line);
            $matchString = "currency='";
            $pos1 = strpos($line, $matchString);
            if ($pos1) {
                $currencySymbol = substr($line, $pos1 + strlen($matchString), 3);
                $matchString = "rate='";
                $pos2 = strpos($line, $matchString);
                $rateString = substr($line, $pos2 + strlen($matchString));
                $pos3 = strpos($rateString, "'");
                $rate = substr($rateString, 0, $pos3);
                $exchangeRates[$currencySymbol] = $rate;
            }
        }
        return new static($exchangeRates);
    }



}