<?php
namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;

class CurrencyUpdateProductPricing{

    public static function run()
    {
        if (\App\Helpers\Cfg::getValue("CurrencyAutoUpdateProductPrices")) {
            \App\Helpers\Currency::currencyUpdatePricing();
            \App\Helpers\LogActivity::Save("Cron Job: Products Updated for Current Rates");
        }
    }
    public static function runOLD($currencyid = ""){
        $result=\App\Models\Currency::where('default',1)->first();
        $defaultcurrencyid = $result->id;
        
        $sql=\App\Models\Currency::select('id','rate');
        if($currencyid){
            $sql->where('id',$currencyid);
        }else{
            $sql->where('id','!=',$defaultcurrencyid);
        }
        $currencies = array();
        $sql=$sql->get();
        foreach($sql as  $r){
            $currencies[$r["id"]] = $r["rate"];
        } 
        
        $pricing=\App\Models\Pricing::where('currency', $defaultcurrencyid)->get();
        foreach($pricing as $data){
            $type      = $data->type;
            $relid     = $data->relid;
            $msetupfee = $data->msetupfee;
            $qsetupfee = $data->qsetupfee;
            $ssetupfee = $data->ssetupfee;
            $asetupfee = $data->asetupfee;
            $bsetupfee = $data->bsetupfee;
            $tsetupfee = $data->tsetupfee;
            $monthly   = $data->monthly;
            $quarterly = $data->quarterly;
            $semiannually = $data->semiannually;
            $annually = $data->annually;
            $biennially = $data->biennially;
            $triennially = $data->triennially;
            if (in_array($type, ["domainregister", "domaintransfer", "domainrenew"])) {
                $domaintype = true;
            } else {
                $domaintype = false;
            }
            if ($type == "configoptions") {
                $negativePriceAllowed = true;
            } else {
                $negativePriceAllowed = false;
            }
            foreach ($currencies as $id => $rate) {
                if ($rate <= 0) {
                    continue;
                }

                $data=\App\Models\Pricing::select('id')->where('type',$type)->where('currency',$id)->where('tsetupfee',$tsetupfee);
                if ($domaintype) {
                    $data->where('tsetupfee',$tsetupfee);
                }
                $data=$data->first();
                //dd($data);
                $pricing_id = $data->id ?? null;
                if (!$pricing_id) {
                    $insert=new \App\Models\Pricing();
                    $insert->type = $type;
                    $insert->currency = $id;
                    $insert->relid = $relid;
                    $insert->tsetupfee = $tsetupfee;
                    $insert->save();
                    $pricing_id =  $insert->id;
                }

                if ($negativePriceAllowed) {
                    $update_msetupfee = round($msetupfee * $rate, 2);
                    $update_qsetupfee = round($qsetupfee * $rate, 2);
                    $update_ssetupfee = round($ssetupfee * $rate, 2);
                    $update_asetupfee = round($asetupfee * $rate, 2);
                    $update_bsetupfee = round($bsetupfee * $rate, 2);
                } else {
                    $update_msetupfee = 0 < $msetupfee ? round($msetupfee * $rate, 2) : $msetupfee;
                    $update_qsetupfee = 0 < $qsetupfee ? round($qsetupfee * $rate, 2) : $qsetupfee;
                    $update_ssetupfee = 0 < $ssetupfee ? round($ssetupfee * $rate, 2) : $ssetupfee;
                    $update_asetupfee = 0 < $asetupfee ? round($asetupfee * $rate, 2) : $asetupfee;
                    $update_bsetupfee = 0 < $bsetupfee ? round($bsetupfee * $rate, 2) : $bsetupfee;
                }

                if ($domaintype) {
                    $update_tsetupfee = $tsetupfee;
                } else {
                    $update_tsetupfee = 0 < $tsetupfee ? round($tsetupfee * $rate, 2) : $tsetupfee;
                }

                if ($negativePriceAllowed) {
                    $update_monthly = round($monthly * $rate, 2);
                    $update_quarterly = round($quarterly * $rate, 2);
                    $update_semiannually = round($semiannually * $rate, 2);
                    $update_annually = round($annually * $rate, 2);
                    $update_biennially = round($biennially * $rate, 2);
                    $update_triennially = round($triennially * $rate, 2);
                } else {
                    $update_monthly = 0 < $monthly ? round($monthly * $rate, 2) : $monthly;
                    $update_quarterly = 0 < $quarterly ? round($quarterly * $rate, 2) : $quarterly;
                    $update_semiannually = 0 < $semiannually ? round($semiannually * $rate, 2) : $semiannually;
                    $update_annually = 0 < $annually ? round($annually * $rate, 2) : $annually;
                    $update_biennially = 0 < $biennially ? round($biennially * $rate, 2) : $biennially;
                    $update_triennially = 0 < $triennially ? round($triennially * $rate, 2) : $triennially;
                }
                $p=\App\Models\Pricing::where('type',$type)->where('currency',$id)->where('relid',$relid);
                //dd($p->toSql());
                if ($domaintype) {
                    $p->where('tsetupfee',$tsetupfee);
                }
                $p->update(["msetupfee" => $update_msetupfee, "qsetupfee" => $update_qsetupfee, "ssetupfee" => $update_ssetupfee, "asetupfee" => $update_asetupfee, "bsetupfee" => $update_bsetupfee, "tsetupfee" => $update_tsetupfee, "monthly" => $update_monthly, "quarterly" => $update_quarterly, "semiannually" => $update_semiannually, "annually" => $update_annually, "biennially" => $update_biennially, "triennially" => $update_triennially]);

            }

        }

    }
}
