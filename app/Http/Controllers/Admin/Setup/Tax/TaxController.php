<?php

namespace App\Http\Controllers\Admin\Setup\Tax;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AdminRole;
use App\Models\Admin;
use App\Helpers\LogActivity;
use DataTables;
use Carbon\Carbon;
use Database;
use Validator;
class TaxController extends Controller{

    public function index()
    {   

        $taxConfig=[
                    'TaxEnabled',
                    'TaxCode',
                    'TaxEnabled',
                    'TaxIDDisabled',
                    'TaxType',
                    'TaxCustomInvoiceNumbering',
                    'TaxCustomInvoiceNumberFormat',
                    'TaxNextCustomInvoiceNumber',
                    'TaxAutoResetNumbering',
                    'TaxVATEnabled',
                    'TaxEUTaxValidation',
                    'TaxEUTaxExempt',
                    'TaxEUHomeCountry',
                    'TaxEUHomeCountryNoExempt',
                    'SequentialInvoiceNumbering',
                    'SequentialInvoiceNumberFormat',
                    'SequentialInvoiceNumberValue',
                    'TaxAutoResetPaidNumbering',
                    'TaxSetInvoiceDateOnPayment',
                    'TaxDomains',
                    'TaxBillableItems',
                    'TaxLateFee',
                    'TaxCustomInvoices',
                    'TaxPerLineItem',
                    'TaxL2Compound',
                    'TaxInclusiveDeduct'
                ];

        //dd($taxConfig);
        $config=array();
        foreach($taxConfig as $r){
            $config[$r]=\App\Helpers\Cfg::get($r);
        }

        //dd($config);


        $countries=(new \App\Helpers\Country())->getCountryNameArray();
        $level=[1,2];
       
        $tagtable=array();
        foreach($level as $r){
            $tax=\App\Models\Tax::where('level',$r)->orderBy("country", "asc")->orderBy("state", "asc")->orderBy("country", "asc")->get();
            $result=array();
            foreach($tax as $t){
                if (array_key_exists($t->country, $countries)) {
                    $country = $countries[$t->country];
                }else{
                    $country = $t->country;
                }
                $state = $t->state;
                if ($state == "") {
                    $state ='Applies to Any State';
                }
                if ($country == "") {
                    $country = 'Applies to Any Country';
                }

                $result[]=[
                            'id'    => $t->id,
                            'name'  => $t->name,
                            'state' => $state,
                            'country' => $country,
                            'taxrate' => (string) $t->taxrate.'%'
                        ];
            }


            $tagtable['level'.$r]=$result;
        }

        
      
        $params=[
                    'country'   => $countries,
                    'tax'       => $tagtable,
                    'config'    => $config
                ];
       
        return view('pages.setup.payments.taxconfiguration.index',$params);
    }

    public function GeneralStore(Request $request){
        //dd($request->all());
        $taxEnabled         =$request->taxenabled;
        $taxType            =$request->tax_code;
        $taxDomains         =$request->taxdomains ?? null;
        $taxBillableItems   =$request->taxbillableitems ?? null;
        $taxLateFee         =$request->taxlatefee;
        $taxCustomInvoices  =$request->taxcustominvoices;
        //ini
        $taxL2Compound      =$request->taxl2compound;
        $taxInclusiveDeduct =$request->taxinclusivededuct;
        $taxPerLineItem     =$request->taxperlineitem;
        //ini
        $taxVatEnabled      =(bool)(int)$request->vatenabled;
        $euTaxValidation    =(bool)(int)$request->eu_tax_validation;
        $euTaxExempt        =(bool)(int)$request->eu_tax_exempt;


        $homeCountry                =$request->home_country;
        $homeCountryExempt          =(bool)(int)$request->home_country_exempt;
        $customInvoiceNumbering     =(bool)(int)$request->custom_invoice_numbering;
        $customInvoiceNumberFormat  =$request->custom_invoice_number_format ?? '{NUMBER}';
        $autoResetNumbering         =$request->custom_invoice_number_reset_frequency;
        $autoResetPaidNumbering     =$request->paid_invoice_number_reset_frequency;
        $setInvoiceDateOnPayment    =(bool)(int)$request->set_invoice_date;
        $sequentialPaidFormat       =$request->sequential_paid_format ?? '{NUMBER}';
        $paidInvoiceNumbering       =(bool) (int)$request->sequential_paid_numbering;
        $taxIdEnabled               =(bool) (int)$request->tax_id_enabled;

        $taxSettings=[
                        "TaxEnabled"                    => $taxEnabled,
                        "TaxType"                       => $taxType,
                        "TaxDomains"                    => $taxDomains,
                        "TaxBillableItems"              => $taxBillableItems,
                        "TaxLateFee"                    => $taxLateFee,
                        "TaxCustomInvoices"             => $taxCustomInvoices,
                        "TaxL2Compound"                 => $taxL2Compound,
                        "TaxInclusiveDeduct"            => $taxInclusiveDeduct,
                        "TaxPerLineItem"                => $taxPerLineItem,
                        "TaxVATEnabled"                 => $taxVatEnabled,
                        "TaxEUTaxValidation"            => $euTaxValidation,
                        "TaxEUHomeCountry"              => $homeCountry,
                        "TaxEUTaxExempt"                => $euTaxExempt,
                        "TaxEUHomeCountryNoExempt"      => $homeCountryExempt,
                        "TaxCustomInvoiceNumbering"     => $customInvoiceNumbering,
                        "TaxCustomInvoiceNumberFormat"  => $customInvoiceNumberFormat,
                        "TaxAutoResetNumbering"         => $autoResetNumbering,
                        "TaxAutoResetPaidNumbering"     => $autoResetPaidNumbering,
                        "TaxSetInvoiceDateOnPayment"    => $setInvoiceDateOnPayment,
                        "SequentialInvoiceNumberFormat" => $sequentialPaidFormat,
                        "SequentialInvoiceNumbering"    => $paidInvoiceNumbering,
                        "TaxCode"                       => $request->tax_code,
                        "TaxIDDisabled"                 => !$taxIdEnabled,
                        "EnableProformaInvoicing"       => $paidInvoiceNumbering
                    ];
        $nextCustomInvoiceNumber = $request->next_custom_invoice_number;
        if ($nextCustomInvoiceNumber && is_numeric($nextCustomInvoiceNumber)) {
            $taxSettings["TaxNextCustomInvoiceNumber"] = $nextCustomInvoiceNumber;
        }
        $nextSequentialInvoiceNumber = $request->next_paid_invoice_number;
        if ($nextSequentialInvoiceNumber && is_numeric($nextSequentialInvoiceNumber)) {
            $taxSettings["SequentialInvoiceNumberValue"] = $nextSequentialInvoiceNumber;
        }
        if(\App\Helpers\Cfg::get('TaxAutoResetNumbering') != $autoResetNumbering ){
            \App\Helpers\Cfg::set('TaxNextCustomInvoiceNumberResetTimestamp','');
        }
        if(\App\Helpers\Cfg::get('TaxAutoResetPaidNumbering') != $autoResetPaidNumbering ){
            \App\Helpers\Cfg::setValue('SequentialInvoiceNumberValueResetTimestamp','');
        }

        $changes = array();
        foreach ($taxSettings as $k => $v) {
            if($k !='TaxEnabled' && \App\Helpers\Cfg::get($k) !=$k ){
                $regEx = "/(?<=[a-z])(?=[A-Z])|(?<=[A-Z][0-9])(?=[A-Z][a-z])/x";
                $friendlySettingParts = preg_split($regEx, $k);
                $friendlySetting = implode(" ", $friendlySettingParts);
                if (in_array($k, array("TaxType", "SequentialInvoiceNumberFormat", "TaxCustomInvoiceNumberFormat"))) {
                    $changes[] = (string) $friendlySetting . " Set to '" . $v . "'";
                }else{
                    if ($v == "on") {
                        $changes[] = (string) $friendlySetting . " Enabled";
                    } else {
                        $changes[] = (string) $friendlySetting . " Disabled";
                    }
                }
                \App\Helpers\Cfg::set($k,$v);
            }
        }

        if ($changes) {
            LogActivity::Save("Tax Configuration: " . implode(". ", $changes) . ".");
        }
        $return=[
                    'error' => false
                ];
        return json_encode($return);
    }

    public function Destroy(Request $request){
        $alert='';
        $error=true;
        $id=(int)$request->id;
        $taxRule=\App\Models\Tax::find($id);
        if(is_null($taxRule)){
            $alert='Error';
        }else{
            $error=false;
            LogActivity::save("Tax Configuration: Level " . $taxRule->level . " Rule Deleted: " . $taxRule->name);
            \App\Models\Tax::find($id)->delete();
        }
        $return=[
                    'error' => $error,
                    'alert' => $alert
                ];
        
        return json_encode($return);
    }

    public function RuleStore(Request $request){
        //dd($request->all());
        $alert='An unknown error occurred';
        $error=true;
        $data=array();
        $name=$request->name ?? 'Tax' ;
        $state=$request->state;
        $country=$request->country;
        $taxRate=$request->taxrate;
        $level=(int)$request->level;
        $countryType=$request->countrytype ?? 'any';
        $stateType=$request->statetype ?? 'any';
        if (!$name) {
            $name = "Tax";
        }
        if ($countryType == "any" && $stateType != "any") {
            $alert='A country must also be selected for a state specific tax rule';
        }else{
            if ($countryType == "any") {
                $country = "";
            }
            if ($stateType == "any") {
                $state = "";
            }
            LogActivity::save("Tax Configuration: Level " . $level . " Rule Added: " . $name);
            $tax=new \App\Models\Tax();
            $tax->level =$level;
            $tax->name =$name;
            $tax->state =$state;
            $tax->country =$country;
            $tax->taxrate =$taxRate;
            $tax->save();
            $id=$tax->id;

            $countries=(new \App\Helpers\Country())->getCountryNameArray();
            $data=\App\Models\Tax::find($id);
            if (array_key_exists($data->country, $countries)) {
                $country = $countries[$data->country];
            }else{
                $country = $data->country;
            }
            $state = $data->state;
            if ($state == "") {
                $state ='Applies to Any State';
            }
            if ($country == "") {
                $country = 'Applies to Any Country';
            }

            $rows=[
                    'id'    => $data->id,
                    'name'  => $data->name,
                    'state' => $state,
                    'country' => $country,
                    'taxrate' => (string) $data->taxrate.'%',
                    'level'   => $data->level
                ];

            $error=false;
            $alert='';
            $data=$rows;

        }

        $return=[
                    'error' => $error,
                    'alert' => $alert,
                    'data'  => $data
                ];

        return json_encode($return);

    }




}
