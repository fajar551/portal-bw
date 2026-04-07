<?php

namespace App\Http\Controllers\Client;

use App\Helpers\Client as ClientHelper;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Helpers\ResponseAPI;
use App\Helpers\SystemHelper;
use App\Helpers\WHMCS_Helper;
use DateInterval;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class _SellDomainLeaseController extends Controller
{
    protected $uid;
    protected $kurs_dollar;
    protected $html_dir;
    protected $module_uri;

    public function __construct()
    {
        $this->uid = Auth::id();
        $this->kurs_dollar = 15000;
        $this->html_dir = '/path/to/html/dir/';
        $this->module_uri = 'http://example.com/';
    }

    public function getRentDomainAll()
    {
        return DB::table('rent_domain')->groupBy('domain')->get();
    }

    public function filterDomainRent($criteria)
    {
        $data = DB::table('rent_domain')
            ->when(!empty($criteria['domain']), function ($query) use ($criteria) {
                return $query->where('domain', 'like', '%' . $criteria['domain'] . '%');
            })
            ->when(!empty($criteria['status']), function ($query) use ($criteria) {
                return $query->where('status', '=', $criteria['status']);
            })
            ->when(!empty($criteria['price']), function ($query) use ($criteria) {
                return $query->where('price', '=', $criteria['price']);
            })
            ->when(!empty($criteria['epp']), function ($query) use ($criteria) {
                return $query->where('epp', '=', $criteria['epp']);
            })  
            ->get();

        return $data;
    }

    public function getAdminPriceIDR($price)
    {
        $price_tier1 = [16, 5000];
        $price_tier2 = [5001, 25000];
        $price_tier3 = [25001, 999999999999];

        $return = [];

        $kurs_dollar = $this->kurs_dollar;

        $price_tier1_idr = array_map(fn($val) => $kurs_dollar * $val, $price_tier1);
        $price_tier2_idr = array_map(fn($val) => $kurs_dollar * $val, $price_tier2);
        $price_tier3_idr = array_map(fn($val) => $kurs_dollar * $val, $price_tier3);

        $admin_price = 999999999999;

        if ($price < $price_tier1_idr[0]) {
            $admin_price = 15 * $kurs_dollar;
            $return['price'] = $admin_price;
            $return['percent'] = '15 USD';
            return $return;
        }

        if ($price >= $price_tier1_idr[0] && $price <= $price_tier1_idr[1]) {
            $admin_price = 0.2 * $price;
            $return['price'] = $admin_price;
            $return['percent'] = '20%';
            return $return;
        }

        if ($price >= $price_tier2_idr[0] && $price <= $price_tier2_idr[1]) {
            $admin_price = (1000 * $kurs_dollar) + (0.15 * $price);
            $return['price'] = $admin_price;
            $return['percent'] = '15% + 1000 USD';
            return $return;
        }

        if ($price >= $price_tier3_idr[0]) {
            $admin_price = (4000 * $kurs_dollar) + (0.1 * $price);
            $return['price'] = $admin_price;
            $return['percent'] = '10% + 4000 USD';
            return $return;
        }
    }

    public function isDomainQword($domain)
    {
        // $command = 'GetClientsDomains';
        $clientHelper = new ClientHelper();
        $postData = [
            'domain' => $domain,
            'domainid' => '',
            'stats' => false,
            'clientid' => $this->uid,
            'limitstart' => 0,
            'limitnum' => 1,
        ];
        $results = $clientHelper->GetClientsDomains_2024($postData);

        return $results['totalresults'] > 0 && $results['domains']['domain'][0]['status'] == 'Active';
    }

    public function notifPemilikDomain($clientid, $domain)
    {
        $systemHelper = new SystemHelper();
        // $command = 'SendEmail';
        $postData = [
            'id' => $clientid,
            'customtype' => 'general',
            'customsubject' => 'Domain Rent / Lease',
            'custommessage' => "<p>Ada invoice paid yang domain rent -{$domain} </p>",
            'customvars' => base64_encode(serialize(["domain" => $domain])),
        ];

        $results = $systemHelper->SendEmail($postData);
        return $results;
    }

    public function detectDomainRent($invoice_id)
    {
        $whmcsHelper = new WHMCS_Helper();
        $postData = ['invoiceid' => $invoice_id];

        $results = $whmcsHelper->GetInvoice($postData);
        $notes = $results['notes'];

        if (strpos($notes, 'domain_lease:') !== false) {
            $domain = str_replace("domain_lease:", "", $notes);
        }
        return $domain;
    }

    public function createInvoiceDomainRent($domain, $clientid)
    {
        $price_obj = DB::table('sell_domain')
            ->where('domain', $domain)
            ->where('clientid', $clientid)
            ->first();

        if (boolval($price_obj->price)) {
            $whmcsHelper = new WHMCS_Helper();
            $postData = [
                'userid' => $clientid,
                'status' => 'Unpaid',
                'paymentmethod' => 'mailin',
                'taxrate' => '11.00',
                'itemdescription1' => 'Domain Lease/Rent :' . $domain,
                'itemamount1' => $price_obj->price,
                'itemtaxed1' => '1',
                'notes' => 'domain_lease:' . $domain
            ];
            $results = $whmcsHelper->CreateInvoice($postData);
        } else {
            $results = 'Internal server error';
        }
        return $results;
    }

    public function insertDomainLease($domain, $price)
    {
        $cek = DB::table('rent_domain')->where('domain', $domain)->first();
        if (!boolval($cek)) {
            DB::table('rent_domain')->insert([
                'clientid' => $this->uid,
                'domain' => $domain,
                'price' => $price,
                'status' => 'NEED_VERIFY',
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function insertEPP($domain, $epp)
    {
        $this->validateDomain($domain);
        try {
            DB::table('rent_domain')
                ->where('domain', $domain)
                ->update(['epp' => $epp]);
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal edit EPP: ' . $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Berhasil edit EPP'
        ];
    }

    public function rent_domain($domain, $clientid, $is_renew = false)
    {
        $price = DB::table('rent_domain')
            ->where('domain', $domain)
            ->first();

        $description = $is_renew ? 'Renew Rent / Lease Domain :' . $domain : 'Rent / Lease Domain :' . $domain;
        $notes = $is_renew ? 'rent_lease_domain_renew:' . $domain : 'rent_lease_domain:' . $domain;

        $client = \App\Models\Client::find($clientid);
        if (!$client) {
            return  redirect()->back()->with([
                'alert-type' => 'danger',
                'alert-message' => 'Invalid client'
            ]);
        }
        $whmcsHelper = new WHMCS_Helper();
        $postData = [
            'userid' => $clientid,
            'status' => 'Unpaid',
            'paymentmethod' => 'banktransfer',
            'taxrate' => '11.00',
            'taxrate2' => '0.00',
            'date' => now()->format('Y-m-d'),
            'duedate' => now()->addDays(7)->format('Y-m-d'),
            'notes' => $notes,
            'itemdescription' => $description,
            'itemamount' => number_format($price->price, 2, '.', ''),
            'itemtaxed' => true,
            'autoapplycredit' => false,
            'sendinvoice' => false,
        ];
        
        $results = $whmcsHelper->CreateInvoice($postData);
        return $results;
        
        //TODO CreateInvoice
        // if (boolval($price)) {
        //     $command = 'CreateInvoice';
        //     $postData = [
        //         'userid' => $clientid,
        //         'status' => 'Unpaid',
        //         'paymentmethod' => 'mailin',
        //         'taxrate' => '10.00',
        //         'itemdescription1' => $description,
        //         'itemamount1' => $price->price,
        //         'itemtaxed1' => '1',
        //         'notes' => $notes
        //     ];

        //     $results = $this->localAPI($command, $postData);
        //     return $results;
        // }
        // return [];
    }

    public function addOneMonth($domain, $clientid, $invoice)
    {
        $que0 = DB::table('rent_domain_transaction')
            ->where('domain', $domain)
            ->where('remind_me', 1)
            ->orderBy('end_rent', 'desc')
            ->first();

        DB::table('rent_domain_transaction')
            ->where('id', $que0->id)
            ->update(['status' => 'DONE']);

        $begintime = new DateTime($que0->end_rent);
        $interval = new DateInterval('P1M');
        $begintime->add($interval);
        $end = $begintime->format('Y-m-d H:i:s');

        $que1 = DB::table('rent_domain')
            ->where('domain', $domain)
            ->where('status', 'VERIFIED')
            ->update(['status' => 'RENT_ACTIVE']);

        $que2 = DB::table('rent_domain_transaction')
            ->insert([
                'domain' => $domain,
                'rental' => $clientid,
                'start_rent' => $que0->end_rent,
                'end_rent' => $end,
                'invoice' => $invoice,
                'status' => 'ACTIVE',
            ]);

        return [$que1, $que2];
    }

    public function generateUniqID()
    {
        return uniqid() . '-' . uniqid();
    }

    public function DBInsertUniqID($domain, $uniqid)
    {
        $isexist = DB::table('domain_uniqid')->where('domain', $domain)->first();
        if (!boolval($isexist)) {
            return DB::table('domain_uniqid')->insert(['domain' => $domain, 'uniqid' => $uniqid]);
        } else {
            return DB::table('domain_uniqid')->where('domain', $domain)->update(['uniqid' => $uniqid]);
        }
    }

    public function generateTokenHTMLFile($domain)
    {
        $uniqid = $this->generateUniqID();
        $uniq_file = 'portal' . $uniqid . '.html';
        $content = 'site-verification: ' . $uniq_file;
        $byte = file_put_contents($this->html_dir . $uniq_file, $content);

        if (boolval($byte)) {
            $resultInsert = $this->DBInsertUniqID($domain, $uniqid);
            if(!$resultInsert) {
                return [
                    'status' => 'error',
                    'message' => 'Gagal Insert Uniq ID'
                ];
            }

            $result = $this->module_uri . 'html/' . $uniq_file;
            return $result;
        }
        return [
            'status' => 'error',
            'message' => 'Can"t write file'
        ];
    }

    public function validateDomain($domain)
    {
        $data = DB::table('rent_domain')->where('clientid', $this->uid)->where('domain', $domain)->first();

        if (boolval($data)) {
            return true;
        } else {
            // die('Domain is not in your account');
            return false;
        }
    }

    public function verifyTokenNameserver($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return false;
        }

        $result = dns_get_record($domain, DNS_TXT);
        $txt = null;

        foreach ($result as $obj) {
            if (strpos($obj['txt'], 'site-verification') !== false) {
                $txt = $obj['txt'];
                break;
            }
        }

        if (boolval($txt)) {
            $txt_mod = str_replace("site-verification:", "", $txt);
            $txt_mod = trim($txt_mod);

            if ($txt_mod == $uniqid) {
                return true;
            }
        }
        return false;
    }

    public function getUniqID($domain)
    {
        $result = DB::table('domain_uniqid')->where('domain', $domain)->first();
        if (boolval($result)) {
            return $result->uniqid;
        }
        return null;
    }

    public function verifyTokenHTML($domain)
    {
        $uniqid = $this->getUniqID($domain);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$domain/portal$uniqid.html");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output == "site-verification: portal$uniqid.html";
    }

    public function getTokenNameserver($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return null;
        }
        return 'site-verification: ' . $uniqid;
    }

    public function getTokenHTML($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return null;
        }
        return 'portal' . $uniqid . '.html';
    }

    public function isDuplicateDomain($domain)
    {
        $duplicate_rent = DB::table('rent_domain')->where('domain', $domain)->first();
        $duplicate_sell = DB::table('sell_domain')->where('domain', $domain)->first();

        return boolval($duplicate_rent) || boolval($duplicate_sell);
    }

    public function startRentFromAdmin($domain)
    {
        $begintime = new DateTime('now');
        $interval = new DateInterval('P1M');
        $begintime->add($interval);
        $end = $begintime->format('Y-m-d H:i:s');
        try {
            DB::table('rent_domain')
                ->where('domain', $domain)
                ->where('status', 'PROCESS_TRANSFER')
                ->update(['status' => 'RENT_ACTIVE']);

            DB::table('rent_domain_transaction')
                ->where('domain', $domain)
                ->where('status', 'PROCESS_TRANSFER')
                ->update([
                    'domain' => $domain,
                    'start_rent' => date('Y-m-d H:i:s'),
                    'end_rent' => $end,
                    'status' => 'ACTIVE',
                ]);
            return [
                'status' => 'success',
                'message' => 'Berhasil Start Rent Domain'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal Start Rent Domain:' . $e->getMessage()
            ];
        }
    }

    public function moveDomainQwords($domain, $clientid)
    {
        DB::table('tbldomains')
            ->where('domain', $domain)
            ->where('status', 'Active')
            ->update(['userid' => $clientid]);
    }

    public function getPublicDomain()
    {
        return DB::table('rent_domain')
            ->where('status', 'VERIFIED')
            ->get();
    }

    public function embedInsert()
    {
        $domain = request()->input('domain');
        $price = request()->input('price');
        $price_sewa = request()->input('price_sewa');
        $type = request()->input('type');

        if (!boolval($price) || !boolval($type) || !boolval($domain) || !boolval($price_sewa)) {
            // $domain = $_POST['domain'];
            // $price = $_POST['price'];
            // $price_sewa =  $_POST['price_sewa'];
            // $type = $_POST['type'];

            return [
                'status' => 'error',
                'message' => 'Required fields are missing'
            ];
        } 

        $inserted = $this->insertDomainLease($domain, $price);

        if ($inserted) {
            $isDomainQword = $this->isDomainQword($domain);
            //cek apakah domain Qwords
            if (!$isDomainQword) {
                $result = $this->generateTokenHTMLFile($domain);
                if($result['status'] == 'error') {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal Generate Token HTML'
                    ]);
                }
                return redirect()->back()->with([
                    'alert-type' => 'success',
                    'alert-message' => 'Berhasil Generate Token HTML'
                ]);

            } 
            try {
                DB::table('rent_domain')
                    ->where('domain', $domain)
                    ->take(1)
                    ->update(['status' => 'VERIFIED']);
            } catch (\Exception $e) {
                return redirect()->back()->with([
                    'alert-type' => 'danger',
                    'alert-message' => 'Gagal Verifikasi Domain Qwords'
                ]);
            }
            return redirect()->back()->with([
                'alert-type' => 'success',
                'alert-message' => 'Berhasil Verifikasi Domain Qwords'
            ]);
        }
    }

    public function embedVerify()
    {
        // $domain = $_POST['domain'];
        // $type = $_POST['type'];
        $domain = request()->input('domain');
        $type = request()->input('type');

        $exist = DB::table('rent_domain')->where('domain', $domain)->first();

        if (boolval($exist)) {
            $valid = $this->validateDomain($domain);
            if (!$valid) {
                return redirect()->back()->with([
                    'alert-type' => 'danger',
                    'alert-message' => 'Domain tidak diizinkan'
                ]);
            }

            if ($type == 'html') {
                $result = $this->verifyTokenHTML($domain);

                if (!$result) {
                    $message_error = 'HTML tidak terverifikasi';
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'HTML tidak terverifikasi'
                    ]);
                    // header("Location: /index.php?m=sell_domain&page=insert&section=modal&domain=$domain&message_error=$message_error");
                    // die();
                } 
                
                try {
                    DB::table('rent_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
                } catch (\Exception $e) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal verifikasi HTML'
                    ]);
                }
                return redirect()->back()->with([
                    'alert-type' => 'success',
                    'alert-message' => 'Sukses verifikasi HTML'
                ]);
            }

            if ($type == 'nameserver') {
                $result = $this->verifyTokenNameserver($domain);
                if (!$result) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Nameserver tidak terverifikasi'
                    ]);
                }

                try {
                    DB::table('rent_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
                } catch (\Exception $e) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal verifikasi Nameserver'
                    ]);
                }
                return redirect()->back()->with([
                    'alert-type' => 'success',
                    'alert-message' => 'Sukses verifikasi Nameserver'
                ]);
            }

            return redirect()->back()->with([
                'alert-type' => 'success',
                'alert-message' => 'Sukses verifikasi'
            ]);
            // header("Location: /index.php?m=sell_domain&message=$message");
            // die();
        }

        return true;
    }
}
    