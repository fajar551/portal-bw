<?php

namespace App\Http\Controllers\Client;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\ResponseAPI;
use App\Helpers\SystemHelper;
use App\Helpers\WHMCS_Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class _AuctionController extends Controller
{
    public function showRouteNotFound()
    {
        echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Route Tidak Ditemukan</title>
                <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    .container {
                        margin-top: 5rem;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Route Auction Tidak Ditemukan</h4>
                        <p>Maaf, route yang Anda tuju tidak ditemukan.</p>
                        <hr>
                        <button class="btn btn-primary" onclick="goToAuction()">Kembali ke Auction</button>
                    </div>
                </div>
                
                <script>
                    public function goToAuction() {
                        window.location.href = "/domains/lelangdomains";
                    }
                </script>
                
                <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            </body>
            </html>';
        return;
    }

    public function buyDomainIDRLelangDev($domain, $clientid)
    {
        $lastPriceObj = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->orderBy('last_price', 'desc')
            ->first();
        $existingBid = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->where('client_id', $clientid)
            ->first();
        $isOwner = DB::table('sell_domain')
            ->where('domain', $domain)
            ->where('clientid', $clientid)
            ->first();
        if ($isOwner) {
            return [
                'status' => 500,
                'message' => "Owner tidak bisa mengikuti lelang"
            ];
        }
        if ($existingBid) {
            return [
                'status' => 500,
                'message' => 'Domain already exist!'
            ];
        }

        $query = DB::table('auction_domain_history')
        ->insert([
            'domain' => $domain,
            'client_id' => $clientid,
            'bid_price' => 0,
            'status_deposit' => 'HOLD',
            'last_price' => $lastPriceObj->last_price,
        ]);

        if ($query) {
            return [
                'status' => 200,
                'message' => 'Successfully Inserted!'
            ];
        } else {
            return [
                'status' => 500,
                'message' => 'Failed to insert bid!'
            ];
        }
    }
    
    public function buyDomainIDR($domain, $clientid, $last_price)
    {
        // Get domain price dari sell_domain
        $price = DB::table('sell_domain')
            ->where('domain', $domain)
            ->where('type', 'FIX_PRICE')
            ->first();
    
        if (!$price || !$price->price) {
            return redirect()->back()->with([
                'alert-type' => 'danger',
                'alert-message' => 'Invalid domain price'
            ]);
        }
    
        // Get client details for tax calculation
        $client = \App\Models\Client::find($clientid);
        if (!$client) {
            return redirect()->back()->with([
                'alert-type' => 'danger',
                'alert-message' => 'Invalid client'
            ]);
        }
    
        try {
            DB::beginTransaction();
    
            // Initialize tax calculator
            $taxCalculator = new \App\Helpers\Tax();
            $taxCalculator->setIsInclusive(\App\Helpers\Cfg::get("TaxType") == "Inclusive")
                         ->setIsCompound(\App\Helpers\Cfg::get("TaxL2Compound"));
    
            $taxrate = $taxrate2 = 0;
            if (\App\Helpers\Cfg::get("TaxEnabled")) {
                // Get Tax Level 1
                $taxdata = \App\Helpers\Invoice::getTaxRate(1, $client->state, $client->country);
                $taxrate = $taxdata["rate"];
    
                // Get Tax Level 2
                $taxdata2 = \App\Helpers\Invoice::getTaxRate(2, $client->state, $client->country);
                $taxrate2 = $taxdata2["rate"];
            }
    
            // Prepare postData for CreateInvoice
            $postData = [
                'userid' => $clientid,
                'status' => 'Unpaid',
                'paymentmethod' => 'banktransfer',
                'taxrate' => number_format($taxrate, 2, '.', ''),
                'taxrate2' => number_format($taxrate2, 2, '.', ''),
                'date' => now()->format('Y-m-d'),
                'duedate' => now()->addDays(7)->format('Y-m-d'),
                'notes' => "sell_domain-buy-{$domain}",
                'itemdescription' => "Purchase Domain: {$domain}",
                'itemamount' => number_format($price->price, 2, '.', ''),
                'itemtaxed' => \App\Helpers\Cfg::get("TaxEnabled") ? true : false,
                'autoapplycredit' => false,
            ];
    
            // Call CreateInvoice
            $invoiceHelper = new \App\Helpers\WHMCS_Helper();
            $invoiceResult = $invoiceHelper->CreateInvoice($postData);
    
            if ($invoiceResult['result'] !== 'success') {
                throw new \Exception($invoiceResult['message']);
            }
    
            $invoiceid = $invoiceResult['invoiceid'] ?? null;
            if (!$invoiceid) {
                throw new \Exception("Invoice ID not returned from CreateInvoice.");
            }
    
            // Check if transfer fee needed
            $parts = explode('.', $domain);
            $extension = count($parts) == 2 ? '.' . $parts[1] : '.' . $parts[1] . '.' . $parts[2];
    
            $active = DB::table('tbldomains')
                ->where('domain', $domain)
                ->where('status', 'Active')
                ->first();
    
            if (!$active) {
                $priceTransfer = DB::table('tbldomainpricing')
                    ->join('tblpricing', 'tbldomainpricing.id', '=', 'tblpricing.relid')
                    ->select('tbldomainpricing.extension', 'tblpricing.relid', 'tblpricing.msetupfee')
                    ->where('tblpricing.type', 'domaintransfer')
                    ->where('tbldomainpricing.extension', $extension)
                    ->where('tblpricing.currency', '1')
                    ->first();
    
                if ($priceTransfer) {
                    // Add transfer fee to tax base
                    $taxCalculator->setTaxBase($taxCalculator->getTaxBase() + $priceTransfer->msetupfee);
    
                    $transferItem = new \App\Models\Invoiceitem();
                    $transferItem->invoiceid = $invoiceid;
                    $transferItem->userid = $clientid;
                    $transferItem->type = 'Domain Transfer';
                    $transferItem->description = "Domain Transfer Fee: {$domain}";
                    $transferItem->amount = $priceTransfer->msetupfee;
                    $transferItem->taxed = \App\Helpers\Cfg::get("TaxEnabled") ? 1 : 0;
                    $transferItem->save();
                }
            }
    
            // Update invoice total
            \App\Helpers\Invoice::UpdateInvoiceTotal($invoiceid);
    
            // Record transaction in sell_domain_invoices
            DB::table('sell_domain_invoices')->insert([
                'domain' => $domain,
                'invoice' => $invoiceid
            ]);
    
            DB::commit();
    
            return [
                'result' => 'success',
                'invoiceid' => $invoiceid
            ];
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create invoice for domain purchase: " . $e->getMessage());
            return ['result' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function buyDomainIDRLelang($domain, $clientid)
    {
        // Mendapatkan harga terakhir untuk domain dari sejarah lelang
        $lastPriceObj = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->orderBy('last_price', 'desc')
            ->first();
        // Memeriksa apakah pengguna sudah menawar pada domain ini
        $existingBid = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->where('client_id', $clientid)
            ->first();
        // Memeriksa apakah pengguna adalah pemilik domain
        $isOwner = DB::table('sell_domain')
            ->where('domain', $domain)
            ->where('clientid', $clientid)
            ->first();
        if ($isOwner) {
            return [
                'status' => 500,
                'message' => "Owner tidak bisa mengikuti lelang"
            ];
        }
        // Jika pengguna sudah menawar sebelumnya
        if ($existingBid) {
            return [
                'status' => 500,
                'message' => 'Domain already exist!'
            ];
        }

        $query = DB::table('auction_domain_history')
        ->insert([
            'domain' => $domain,
            'client_id' => $clientid,
            'bid_price' => 0,
            'status_deposit' => 'HOLD',
            'last_price' => $lastPriceObj->last_price,
        ]);

        // Cek apakah insert berhasil
        if ($query) {
            return [
                'status' => 200,   
                'message' => 'Successfully Inserted!'
            ];
        } else {
            return [
                'status' => 500,
                'message' => 'Failed to insert bid!'
            ];
        }
    }
    
    public function sendEmailToLastClient($last_client_bid, $domain, $bid_value)
    {
        $whmcsHelper = new SystemHelper();
        $postData = [
            'id' => $last_client_bid['client_id'],
            'customtype' => 'general',
            'customsubject' => "Domain $domain ada bid lebih besar",
            'custommessage' => "<p>Ada yang ngebid dengan nominal Rp {$bid_value} pada {$last_client_bid['updated_at']} dan total bid terakhir Rp {$last_client_bid['last_price']}</p>",
            'customvars' => base64_encode(serialize([
                "bid_price" => $last_client_bid['bid_price'],
                "updated_at" => $last_client_bid['updated_at'],
                "last_price" => $last_client_bid['last_price'] + $this->getTotalOrder($domain, null),
            ])),
        ];
        return $whmcsHelper->SendEmail($postData);
    }

    public function sendEmailToLastClientFromSellDomain($from_sell_domain, $domain, $bid_value)
    {
        $total_rp = $bid_value * 1000;
        $whmcsHelper = new SystemHelper();
        $postData = [
            'id' => $from_sell_domain->owner,
            'customtype' => 'general',
            'customsubject' => "Bid Sell Domain",
            'custommessage' => "<p>Ada bid domain $domain sejumlah Rp $total_rp</p>",
        ];

        return $whmcsHelper->SendEmail($postData);
    }

    public function addBid($domain, $bid_value, $anon_email = 0){
        $auth = Auth::user();
        $clientid = $auth->id;

        $last_price = DB::table('auction_domain_history')->where('domain', $domain)->max('last_price');
        $bid_price = $bid_value * 1000;
        
        $removed = $this->getCreditRemoved($clientid, $domain, $bid_value);

        $data = [
            'domain' => $domain, 
            'client_id' => $clientid, 
            'bid_price'=> $bid_price, 
            'last_price' => $last_price + $bid_price,
            'removed_credit' => $removed,
            'note' => "new rule no deposit",
        ];
        
        if (boolval($anon_email)){
            $data['anon_email'] = 1; 
        }
                
        $query = DB::table('auction_domain_history')->insert(
            $data
        );
        
        return $query; 
    }

    public function getAuctionDataWithLastPrice() {
        $data = DB::table('auction_domain')
            ->leftJoin('auction_domain_history', 'auction_domain.domain', '=', 'auction_domain_history.domain')
            ->select('auction_domain.*', DB::raw('COALESCE(MAX(auction_domain_history.last_price), auction_domain.price) as last_price'))
            ->groupBy('auction_domain.id')
            ->orderBy('auction_domain_history.updated_at', 'desc') 
            ->get();

        return $data;
    }
    
    function getData($domain){
        $data = DB::table('auction_domain')
            ->where('domain', $domain)
            ->get();
        foreach ($data as $value){
            $last_price = $this->getLastPrice($value->domain);
            $value->price = $last_price; 
        }
        return $data; 
    }
    
    public function getCreditRemoved($clientid, $domain, $bid_value){
        // $domain = 'coinone.co.id'; 
        // $bid_value = 10;
        // $clientid = 51446; // 990000.27
        //$clientid = 39199; // 940000.00
        
        $last_price = DB::table('auction_domain_history')->where('domain', $domain)->max('last_price');
        $bid_price = $bid_value * 1000;
        $deposit =  DB::table('tblclients')->where('id', $clientid)->first()->credit;
        
        // calculate removed deposit
        $total_bid = $last_price + $bid_price;
        $total_your_bid_before = DB::table('auction_domain_history')->where('domain', $domain)->where('client_id', $clientid)->sum('removed_credit');
        $credit_remove = $total_bid - $total_your_bid_before;
        
        return $credit_remove;
    }

    public function getLastPrice($domain)
    {
        $lastPrice = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->max('last_price');

        return $lastPrice;
    }

    public function apakahAdaDi1JamTerakhir($time) {
        $waktuSekarang = new \DateTime("now");
        $waktuYangDiberikan = new \DateTime($time);
        $waktuSatuJamSebelum = clone $waktuYangDiberikan;
        $waktuSatuJamSebelum->sub(new \DateInterval('PT1H'));
        
        if ($waktuSekarang >= $waktuSatuJamSebelum && $waktuSekarang <= $waktuYangDiberikan) {
            return true;
        } else {
            return false;
        }
    }

    public function tambahWaktuLelang($percobaan_ke, $domain)
    {
        if (!boolval($domain) || !boolval($percobaan_ke)) {
            die("invalid input. -- code:_Auction-1");
        }

        $getclosedate = DB::table('auction_domain')
            ->where('domain', $domain)
            ->select(['close_date'])
            ->first();
        $closedate = $getclosedate->close_date;

        if ($percobaan_ke > 5) {
            return $closedate;
        }

        switch ($percobaan_ke) {
            case 1:
                $addhour = 8;
                break;
            case 2:
                $addhour = 7;
                break;
            case 3:
                $addhour = 6;
                break;
            case 4:
                $addhour = 5;
                break;
            case 5:
                $addhour = 4;
                break;
            default:
                $addhour = 0;
        }

        $parse_closedate = new \DateTime($closedate);
        $interval = new \DateInterval('PT' . $addhour . 'H');
        $parse_closedate->add($interval);

        return $parse_closedate->format('Y-m-d H:i:s');
    }

    public function db_perpanjangLelang($closedate, $domain)
    {
        $result = DB::table('auction_domain')
            ->where('domain', $domain)
            ->update(['close_date' => $closedate]);
        DB::table('auction_domain')->where('domain', $domain)->increment('maxtry');

        return $result;
    }

    public function getMaxtry($domain)
    {
        $result = DB::table('auction_domain')
            ->where('domain', $domain)
            ->first();

        return $result->maxtry;
    }

    public function getDeposit()
    {
        $auth = Auth::user();
        $clientid = $auth->id;

        $credit =  DB::table('tblclients')->where('id', $clientid)->first()->credit;

        return $credit;
    }

    public function checkDeposit()
    {
        $auth = Auth::user();
        $clientid = $auth->id;

        $credit =  DB::table('tblclients')->where('id', $clientid)->first()->credit;

        return $credit > 0;
    }

    public function getHistory($domain) {
        $data = DB::table('auction_domain_history')
            ->join('tblclients', 'auction_domain_history.client_id', '=', 'tblclients.id')
            ->select('auction_domain_history.*', 'tblclients.email')
            ->where('domain', $domain)
            ->where('status_deposit', 'HOLD')
            ->orderBy('updated_at', 'desc')
            ->get();
        $mapped = $data->map(function($val) {
            $obfuscatedUser = $this->obfuscate_email((object)[
                'user' => $val->email
            ]);
    
            return (object) [
                "last_price" => $val->last_price,
                "bid" => $val->removed_credit,
                "removed_credit" => $val->removed_credit,
                "bid_price" => $val->bid_price,
                "user" => $obfuscatedUser->user,
                "date" => $val->updated_at
            ];
        });
        
        return $mapped; 
    }
    
    public function GetAllHistory($domain, $clientid) {
        $all_history = DB::table('auction_domain_history')
            ->join('auction_domain', 'auction_domain.domain', '=', 'auction_domain_history.domain')
            ->select('auction_domain_history.*', 'auction_domain.owner')
            ->where('auction_domain_history.domain', $domain)
            ->get();
    
        $all_history = $all_history->map(function ($val) use ($domain) {
            if (boolval($val->owner)) {
                return $val;
            }
    
            $total_order = $this->getTotalOrder($val->domain, null);
            $val->last_price += $total_order;
    
            return $val;
        })->sortByDesc('updated_at');
    
        return $all_history;
    }
    
    public function getDetail($domain)
    {
        $last_price = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->orderBy('last_price', 'desc')
            ->first();

        $data = DB::table('auction_domain')->where('domain', $domain)->first();
        if (!$data) {
            return null;
        }

        // Jika last_price tidak ada, set nilai default
        $last_bid_price = $last_price ? $last_price->last_price : 0;
        $last_bid_date = $last_price ? $last_price->updated_at : null;

        // Buat objek dengan detail yang diperlukan
        $result = (object) [
            "domain" => $data->domain,
            "initial_price" => $data->price,
            "last_price" => $last_bid_price,
            "last_bid_date" => $last_bid_date,
            "open_date" => $data->open_date,
            "close_date" => $data->close_date
        ];
        return $result;
    }

    public function validasiBidTerlaluBesar($domain, $bid_value)
    {
        $highest_obj = DB::table('auction_domain_history')->where('domain', $domain)->orderBy('bid_price', 'desc')->first();
        $highest_bid = $highest_obj ? $highest_obj->bid_price : 0;
        $current_bid = $bid_value * 1000;
    
        if ($highest_bid == 0) {
            return true;
        }
    
        $highest_bid_length = strlen((string)$highest_bid);
        $current_bid_length = strlen((string)$current_bid);
    
        if ($highest_bid_length <= 5) {
            return $current_bid < 80000000;
        } elseif ($highest_bid_length == 6) {
            return $current_bid < 80000000;
        } elseif ($highest_bid_length == 7) {
            return $current_bid < 800000000;
        }
    
        return true;
    }
    
    public function obfuscate_email($collection)
    {
        // Check if the input is a collection
        if ($collection instanceof \Illuminate\Support\Collection) {
            return $collection->map(function ($item) {
                // Check if the user property exists
                if (isset($item->user)) {
                    $emailParts = explode("@", $item->user);
                    $username = $emailParts[0];
    
                    // Ensure at least 3 characters are visible
                    $visibleLength = min(3, strlen($username));
    
                    $obfuscatedEmail = $item->user == 'hidden' ? 'hidden' :
                        substr($username, 0, $visibleLength) .
                        str_repeat('*', 12);
    
                    $item->user = $obfuscatedEmail;
                }
                return $item;
            });
        }
    
        // If it's not a collection, handle it as a single object
        if (isset($collection->user)) {
            $emailParts = explode("@", $collection->user);
            $username = $emailParts[0];
    
            // Ensure at least 3 characters are visible
            $visibleLength = min(3, strlen($username));
    
            $obfuscatedEmail = $collection->user == 'hidden' ? 'hidden' :
                substr($username, 0, $visibleLength) .
                str_repeat('*', 12);
    
            $collection->user = $obfuscatedEmail;
        }
    
        return $collection;
    }
    
      public function getTotalOrder($domain, $clientid)
    {
        // Check if the client ID is provided and valid
        if (boolval($clientid)) {
            $is_owner = DB::table('auction_domain')->where('owner', $clientid)->first();
        } else {
            $is_owner = false;
        }
    
        // If the client is the owner, return 0
        if (boolval($is_owner)) {
            return 0;
        }
    
        // Check if the domain is valid
        if (is_null($domain) || !strpos($domain, '.')) {
            // Handle the case where the domain is null or improperly formatted
            return 0; // or handle it as per your business logic
        }
    
        $explode = explode('.', $domain);
        $price = 0;
    
        if (isset($explode[2])) {
            $jointld = "." . $explode[1] . "." . $explode[2];
            $count = strlen($explode[0]);
    
            $priceRecord = DB::table('tblpricing')
                ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                ->where('tbldomainpricing.extension', $jointld)
                ->where('tblpricing.type', 'domainrenew')
                ->where('tblpricing.currency', 1)
                ->first();
            if ($priceRecord) {
                $price += $priceRecord->msetupfee;
            }
    
            switch ($count) {
                case 2:
                    $price += 17000000;
                    break;
                default:
                    $price += 0;
                    break;
            }
        } elseif (isset($explode[1])) {
            $jointld = "." . $explode[1];
    
            $priceRecord = DB::table('tblpricing')
                ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                ->where('tbldomainpricing.extension', $jointld)
                ->where('tblpricing.type', 'domainrenew')
                ->where('tblpricing.currency', 1)
                ->first();
    
            if ($priceRecord) {
                $price += $priceRecord->msetupfee;
            }
            if ($jointld == '.id') {
                $count = strlen($explode[0]);
                switch ($count) {
                    case 2:
                        $price += 500000000;
                        break;
                    case 3:
                        $price += 15000000;
                        break;
                    case 4:
                        $price += 2250000;
                        break;
                    default:
                        $price += 0;
                        break;
                }
            }
        }
    
        $backorder_price = DB::table('tblpricing')
            ->where('relid', 382)
            ->where('type', 'product')
            ->where('currency', 1)
            ->first();
    
        if ($backorder_price) {
            $backorder_price = $backorder_price->monthly;
            return $price + $backorder_price;
        } else {
            return $price;
        }
    }


    public function cekMinimalBid($domain, $value)
    {
        $firsttime = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->where('status_deposit', 'HOLD')
            ->orderBy('updated_at', 'desc')->first();
        $is_firsttime_bid = $firsttime->bid_price > 0 ? false : true;

        if ($is_firsttime_bid) {
            if ($value < 100) {
                return false;
            }
        } else {
            if ($value < 15) {
                return false;
            }
        }

        return true;
    }

    public function maxBidBerurutan($clientid, $domain)
    {

        $datas = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->orderBy('last_price', 'desc')
            ->limit(2)
            ->get()->toArray();
        $datasfil = array_filter($datas, function ($val) use ($clientid) {
            return $val->client_id == $clientid;
        });
        $max_bid_berurutan = count($datasfil);
        return $max_bid_berurutan;
    }

    public function checkUserAllowedViewAuction($domain, $clientid)
    {
        $data = DB::table('tblcustomfieldsvalues')
            ->join('tblhosting', 'tblcustomfieldsvalues.relid', '=', 'tblhosting.id')
            ->where('value', $domain)
            ->where('userid', $clientid)
            ->whereIn('domainstatus', ['Active', 'Completed'])
            ->where('fieldid', 1215) 
            ->first();

        if (boolval($data) == false) {
            $data = DB::table('auction_domain')
                ->join('auction_domain_history', 'auction_domain.domain', '=', 'auction_domain_history.domain')
                ->where('auction_domain.status', 'OPEN_LELANG')
                ->where('auction_domain_history.client_id', $clientid)
                ->where('auction_domain.domain', $domain)
                ->first();
        }

        $result = boolval($data);

        return $result;
    }

    public function getAuctionData($clientid = 0, $waktu, $tipe) {
        // Mendapatkan domain yang tidak boleh diikutkan
        $domain_not_included = DB::table('auction_domain_history')
            ->where('status_deposit', 'HOLD')
            ->where('client_id', $clientid)
            ->groupBy('domain')
            ->pluck('domain')
            ->toArray();
    
        // Menentukan kondisi umum untuk query
        $query = DB::table('auction_domain')
            ->join('auction_domain_history', 'auction_domain_history.domain', '=', 'auction_domain.domain')
            ->where('status_deposit', 'HOLD')
            ->where('auction_domain.status', 'OPEN_LELANG')
            ->groupBy('auction_domain_history.domain');
    
        // Menentukan tipe lelang
        if ($tipe == 'backorder') {
            $query->whereNull('owner');
        } elseif ($tipe == 'client') {
            $query->whereNotNull('owner');
        }
        // Mengatur pengurutan berdasarkan waktu
        if ($waktu == 'waktu-terdekat') {
            $query->orderByRaw('auction_domain.close_date IS NULL, auction_domain.close_date ASC');
        } elseif ($waktu == 'waktu-terlama') {
            $query->orderByRaw('auction_domain.close_date IS NULL, auction_domain.close_date DESC');
        }
    
        $data = $query->get(['auction_domain_history.*', 'auction_domain.*', DB::raw('MAX(last_price) as last_price')])->toArray();
        // Memfilter data untuk client tertentu
        if ($clientid == 56377) {
            $data = array_filter($data, function ($item) {
                return strtolower($item->domain) != 'prelo.co.id';
            });
        }
    
        // Memproses data
        foreach ($data as $val) {
            if ($val->owner == null) {
                $val->last_price = $this->getTotalOrder($val->domain, null) + $val->last_price;
            }
            $val->allowed = in_array($val->domain, $domain_not_included);
        }
    
        return $data;
    }
    
    public function getAll($clientid = 0)
    {
        $domain_not_included = DB::table('auction_domain_history')
            ->where('status_deposit', 'HOLD')
            ->where('client_id', $clientid)
            ->groupBy('domain')
            ->pluck('domain')
            ->toArray();

        $data = DB::table('auction_domain')
            ->join('auction_domain_history', 'auction_domain_history.domain', '=', 'auction_domain.domain')
            ->where('auction_domain_history.status_deposit', 'HOLD')
            ->where('auction_domain.status', 'OPEN_LELANG')
            // ->whereNotIn('auction_domain_history.domain', $domain_not_included)
            ->orderBy('close_date', 'ASC')
            ->groupBy('auction_domain_history.domain')
            ->select('auction_domain_history.*', 'auction_domain.*', DB::raw('MAX(auction_domain_history.last_price) as last_price'))
            ->get()->toArray();

        foreach ($data as $val) {
            if (is_null($val->owner)) {
                $val->last_price = $this->getTotalOrder($val->domain, null) + $val->last_price;
            }

            $val->allowed = !in_array($val->domain, $domain_not_included);
        }

        return $data;
    }

    public function getPriceDomain($domain)
    {
        $explode = explode('.', $domain);
        
        if (isset($explode[2])) {
            $jointld = "." . $explode[1] . "." . $explode[2];
            $domainLength = strlen($explode[0]); 
            
            $priceData = DB::table('tblpricing')
                ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                ->where('tbldomainpricing.extension', $jointld)
                ->where('tblpricing.type', 'domainrenew')
                ->where('tblpricing.currency', 1)
                ->first();
            
            $price = $priceData ? $priceData->msetupfee : 0; 
            
            // Menambahkan biaya berdasarkan panjang nama domain
            switch ($domainLength) {
                case 2:
                    $price += 17000000; // Biaya tambahan untuk panjang 2 karakter
                    break;
                default:
                    $price += 0; // Tidak ada biaya tambahan
                    break;
            }
        } else {
            // Jika hanya ada dua bagian (domain dan TLD)
            $jointld = "." . $explode[1];
            $priceData = DB::table('tblpricing')
                ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                ->where('tbldomainpricing.extension', $jointld)
                ->where('tblpricing.type', 'domainrenew')
                ->where('tblpricing.currency', 1)
                ->first();
            
            // Mengambil harga dasar
            $price = $priceData ? $priceData->msetupfee : 0; // Menangani kemungkinan tidak ada data
    
            // Jika TLD adalah '.id', tentukan biaya tambahan berdasarkan panjang nama domain
            if ($jointld == '.id') {
                $domainLength = strlen($explode[0]); // Menghitung panjang nama domain
                
                switch ($domainLength) {
                    case 2:
                        $price += 500000000; // Biaya untuk panjang 2 karakter
                        break;
                    case 3:
                        $price += 15000000; // Biaya untuk panjang 3 karakter
                        break;
                    case 4:
                        $price += 2250000; // Biaya untuk panjang 4 karakter
                        break;
                    default:
                        $price += 0; // Tidak ada biaya tambahan
                        break;
                }
            }
        }
        return $price;
    }

    function getAllAuction (){
        $all_auction = DB::table('auction_domain')->get();
        return $all_auction;
    }
} 
