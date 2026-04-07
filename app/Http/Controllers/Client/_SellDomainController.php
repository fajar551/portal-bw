<?php

namespace App\Http\Controllers\Client;

use App\Helpers\Client;
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

class _SellDomainController extends Controller
{
    protected $uid;
    protected $module_uri;
    protected $html_dir;
    protected $kurs_dollar;
    protected $ppn;

    public function __construct()
    {
        $this->uid = Auth::id();
        $this->kurs_dollar = 15000;
        $this->html_dir = '/path/to/html/dir/';
        $this->module_uri = 'http://example.com/';
        $this->ppn = 11;
    }

    public function cekSudahAdaYgNgebid($domain)
    {
        $res = DB::table('auction_domain_history')
            ->where('domain', $domain)
            ->where('bid_price', '>', 0)
            ->where('status', 'ACTIVE')
            ->first();
        return boolval($res);
    }

    public function deleteDomain($domain)
    {
        $return = [];
        try {
            if ($this->cekSudahAdaYgNgebid($domain)) {
                throw new \Exception('Domain sudah ada yang nge BID');
            }

            if (!boolval($this->uid)) {
                throw new \Exception('UID false');
            }

            $res = DB::table('sell_domain')
                ->where('clientid', $this->uid)
                ->where('domain', $domain)
                ->whereIn('status', ['VERIFIED', 'NEED_VERIFY'])
                ->delete();

            $history = DB::table('auction_domain_history')
                ->where('client_id', $this->uid)
                ->where('domain', $domain)
                ->delete();

            $auction = DB::table('auction_domain')
                ->where('owner', $this->uid)
                ->where('domain', $domain)
                ->delete();

            $return['data'] = $res;
            $return["message"] = "Hapus domain " . $domain . " berhasil!";

        } catch (\Exception $e) {
            $return["message"] = $e->getMessage();
        }

        return $return;
    }

    public function enableDomain($domain, $value)
    {
        $return = [];
        try {
            if ($this->cekSudahAdaYgNgebid($domain)) {
                $cek_sell = DB::table('sell_domain')->where('domain', $domain)->where('type', 'AUCTION_PRICE')->first();
                if (!$cek_sell) {
                    throw new \Exception('Domain sudah ada yang nge BID');
                }
            }

            if (!boolval($this->uid)) {
                throw new \Exception('UID false');
            }

            if ($value) {
                $getsell = DB::table('sell_domain')
                    ->where('clientid', $this->uid)
                    ->where('domain', $domain)
                    ->where('status', 'VERIFIED')
                    ->where('type', 'AUCTION_PRICE')
                    ->first();

                if ($getsell->type == 'AUCTION_PRICE') {
                    $open_date = now();
                    $close_date = now()->addDays(10);

                    $exist_auction = DB::table('auction_domain')
                        ->where('domain', $domain)
                        ->where('status', '!=', 'SELL_DOMAIN')
                        ->first();

                    if (boolval($exist_auction)) {
                        try {
                            DB::insert('INSERT INTO auction_domain_old SELECT * FROM auction_domain WHERE domain=? AND status !="SELL_DOMAIN"', [$domain]);
                            DB::insert('INSERT INTO auction_domain_history_old SELECT * FROM auction_domain_history WHERE domain=?', [$domain]);
                        } catch (\Exception $e) {
                            // Log the \exception
                            Log::error('Error copying auction data: ' . $e->getMessage());
                        }

                        DB::table('auction_domain')
                            ->where('domain', $domain)
                            ->where('status', '!=', 'SELL_DOMAIN')
                            ->update([
                                'open_date' => $open_date,
                                'close_date' => $close_date,
                                'status' => 'OPEN_LELANG',
                                'maxtry' => 1,
                                'price' => $getsell->price,
                                'owner' => $this->uid
                            ]);

                        DB::table('auction_domain_history')
                            ->where('domain', $domain)
                            ->delete();

                    } else {
                        DB::table('auction_domain')
                            ->insert([
                                'domain' => $domain,
                                'owner' => $this->uid,
                                'open_date' => $open_date,
                                'close_date' => $close_date,
                                'price' => $getsell->price,
                                'status' => 'OPEN_LELANG'
                            ]);
                    }

                    $getHistory = DB::table('auction_domain_history')
                        ->where('domain', $domain)
                        ->where('client_id', $getsell->clientid)
                        ->first();

                    if (!boolval($getHistory)) {
                        DB::table('auction_domain_history')->insert([
                            'domain' => $domain,
                            'client_id' => $getsell->clientid,
                            'bid_price' => 0,
                            'last_price' => $getsell->price,
                        ]);
                    }
                } else {
                    $getsell = DB::table('sell_domain')
                        ->where('clientid', $this->uid)
                        ->where('domain', $domain)
                        ->where('status', 'VERIFIED')
                        ->where('type', 'FIX_PRICE')
                        ->first();

                    $exist_auction = DB::table('auction_domain')
                        ->where('domain', $domain)
                        ->first();

                    if (!boolval($exist_auction)) {
                        DB::table('auction_domain')
                            ->insert([
                                'domain' => $domain,
                                'owner' => $this->uid,
                                'open_date' => null,
                                'close_date' => null,
                                'price' => $getsell->price,
                                'status' => 'SELL_DOMAIN'
                            ]);
                    } else {
                        DB::table('auction_domain')
                            ->where('domain', $domain)
                            ->update([
                                'status' => 'SELL_DOMAIN',
                                'open_date' => null,
                                'close_date' => null,
                            ]);
                    }
                }
            } else {
                DB::table('auction_domain')
                    ->where('domain', $domain)
                    ->update([
                        'open_date' => null,
                        'close_date' => null,
                        'status' => 'DISABLED'
                    ]);
            }

            $res = DB::table('sell_domain')
                ->where('clientid', $this->uid)
                ->where('domain', $domain)
                ->where('status', 'VERIFIED')
                ->update(['enabled' => $value]);
            $return['data'] = $res;
            $return["message"] = "Sukses Proses Data";

        } catch (\Exception $e) {
            $return["message"] = $e->getMessage();
        }

        return $return;
    }

    public function getDomains()
    {
        $clientHelper = new Client();
        $params = [
            'clientid' => $this->uid,
            'limitstart' => 0,
            'limitnum' => 100,
            'domainid' => '',
            'domain' => '',
        ];
    
        $results = $clientHelper->GetClientsDomains_2024($params);
    
        if ($results['result'] == 'success' && isset($results['domains']['domain'])) {
            $activeDomains = array_filter($results['domains']['domain'], function ($domain) {
                return $domain['status'] == 'Active';
            });
    
            $mappedDomains = array_map(function ($domain) {
                return [
                    'domainname' => $domain['domainname'],
                    'expirydate' => $domain['expirydate'],
                ];
            }, $activeDomains);
    
            return $mappedDomains;
        }
    
        return [];
    }

    public function isDomainQword($domain)
    {
        $clientHelper = new Client();
        $postData = [
            'clientid' => $this->uid,
            'domain' => $domain,
            'domainid' => '',
            'stats' => false,
            'limitstart' => 0,
            'limitnum' => 1,
        ];
        $results = $clientHelper->GetClientsDomains_2024($postData);
        return $results['totalresults'] > 0 && $results['domains']['domain'][0]['status'] == 'Active';
    }

    public function getMySellDomains()
    {
        // $data = Capsule::table('sell_domain')
        //     ->select(['sell_domain.*', 'tbldomains.domain as qword_domain', 'tbldomains.status as qword_status'])
        //     ->leftjoin('tbldomains', function ($join) {
        //             $join->on('tbldomains.domain','=','sell_domain.domain')
        //                  ->where('tbldomains.status', '=', 'Active');
        //         })
        //     ->where('clientid', $this->uid)
        //     ->orderBy('sell_domain.created_at', 'desc')
        //     ->get();
        $data = DB::select('SELECT domain, status, enabled, epp, notes, seller_note, type,
            (SELECT price FROM sell_domain WHERE domain=h.domain AND type="auction_price" LIMIT 1) as auction_price,
            (SELECT price FROM sell_domain WHERE domain=h.domain AND type="fix_price" LIMIT 1) as fix_price
            FROM `sell_domain` as h WHERE clientid=? GROUP BY domain', [$this->uid]);
        return $data;
    }

    public function getBankClient($clientid)
    {
        // $uid = $clientid ?: $this->uid;
        try {
            $bank = DB::table('sell_domain_config')
                ->where('clientid', $clientid)
                ->where('field', 'bank')
                ->first();
            $rekening = DB::table('sell_domain_config')
                ->where('clientid', $clientid)
                ->where('field', 'rekening')
                ->first();
            $atasnama = DB::table('sell_domain_config')
                ->where('clientid', $clientid)
                ->where('field', 'atasnama')
                ->first();
        } catch (\Exception $e) {
            return null;
        }
        if (boolval($bank)) {
            return (object)[
                'bank' => $bank->value,
                'rekening' => $rekening->value,
                'atasnama' => $atasnama->value
            ];
        }
        return null;
    }

    public function getPrice($domain)
    {
        $kontan = DB::table('sell_domain')
            ->where('clientid', $this->uid)
            ->where('domain', $domain)
            ->where('type', 'FIX_PRICE')
            ->first()->price ?? 0;
    
        $awal = DB::table('sell_domain')
            ->where('clientid', $this->uid)
            ->where('domain', $domain)
            ->where('type', 'AUCTION_PRICE')
            ->first()->price ?? 0;
    
        $sewa = DB::table('rent_domain')
            ->where('clientid', $this->uid)
            ->where('domain', $domain)
            ->first()->price ?? 0;
    
        return [
            'awal' => $awal,
            'sewa' => $sewa,
            'kontan' => $kontan
        ];
    }

    public function getDomainAll()
    {
        try {
            $result = DB::table('sell_domain')
                ->leftJoin('tbldomains', 'sell_domain.domain', '=', 'tbldomains.domain')
                ->select(
                    'sell_domain.*',
                    DB::raw('MAX(CASE WHEN sell_domain.type = "AUCTION_PRICE" THEN sell_domain.price ELSE NULL END) as auction_price'),
                    DB::raw('MAX(CASE WHEN sell_domain.type = "FIX_PRICE" THEN sell_domain.price ELSE NULL END) as fix_price'),
                    DB::raw('CASE WHEN tbldomains.status = "Active" THEN "Active" ELSE "" END as qword_status'),
                    DB::raw('"" as nameserver')
                )
                ->groupBy('sell_domain.domain')
                ->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $result;
    }

    public function getDomainAllAdmin()
    {
        try {
            $result = DB::table('sell_domain')
                ->leftJoin('auction_domain', 'sell_domain.domain', '=', 'auction_domain.domain')
                ->leftJoin('tbldomains', 'sell_domain.domain', '=', 'tbldomains.domain')
                ->select(
                    'sell_domain.*',
                    DB::raw('COALESCE(auction_domain.open_date, "") as open_date'),
                    DB::raw('COALESCE(auction_domain.close_date, "") as close_date'),
                )
                ->groupBy('sell_domain.domain')
                ->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $result;
    }

    public function filterDomain($criteria)
    {
        // Ensure all criteria keys exist with default values
        $criteria['domain'] = $criteria['domain'] ?? '';
        $criteria['status'] = $criteria['status'] ?? '';
        $criteria['type'] = $criteria['type'] ?? '';
        $criteria['open_lelang'] = $criteria['open_lelang'] ?? [];
        $criteria['close_lelang'] = $criteria['close_lelang'] ?? [];
    
        // Filter out empty values
        $criteria['open_lelang'] = array_filter($criteria['open_lelang']);
        $criteria['close_lelang'] = array_filter($criteria['close_lelang']);
        $criteria = array_filter($criteria);
    
        $data = DB::table('sell_domain')
            ->leftJoin('auction_domain', 'auction_domain.domain', '=', 'sell_domain.domain')
            ->select([
                'sell_domain.id', 
                'sell_domain.clientid', 
                'sell_domain.domain', 
                'sell_domain.uniqid',
                'sell_domain.status', 
                'sell_domain.price', 
                'sell_domain.type', 
                DB::raw('COALESCE(sell_domain.epp, "") as epp'),
                'sell_domain.invoiceid', 
                'sell_domain.created_at', 
                DB::raw('COALESCE(auction_domain.open_date, "") as open_date'),
                DB::raw('COALESCE(auction_domain.close_date, "") as close_date')
            ])
            ->when(!empty($criteria['status']), function ($query) use ($criteria) {
                return $query->where('sell_domain.status', '=', $criteria['status']);
            })
            ->when(!empty($criteria['domain']), function ($query) use ($criteria) {
                return $query->where('sell_domain.domain', 'like', '%' . $criteria['domain'] . '%');
            })
            ->when(!empty($criteria['type']), function ($query) use ($criteria) {
                return $query->where('sell_domain.type', '=', $criteria['type']);
            })
            ->when(!empty($criteria['open_lelang']), function ($query) use ($criteria) {
                if (!empty($criteria['open_lelang'][0]) && !empty($criteria['open_lelang'][1])) {
                    return $query->whereBetween('auction_domain.open_date', $criteria['open_lelang']);
                } else {
                    if (!empty($criteria['open_lelang'][0])) {
                        return $query->where('auction_domain.open_date', 'like', '%' . $criteria['open_lelang'][0] . '%');
                    } elseif (!empty($criteria['open_lelang'][1])) {
                        return $query->where('auction_domain.open_date', 'like', '%' . $criteria['open_lelang'][1] . '%');
                    } else {
                        return $query->whereRaw('auction_domain.open_date LIKE "%"');
                    }
                }
            })
            ->when(!empty($criteria['close_lelang']), function ($query) use ($criteria) {
                if (!empty($criteria['close_lelang'][0]) && !empty($criteria['close_lelang'][1])) {
                    return $query->whereBetween('auction_domain.close_date', $criteria['close_lelang']);
                } else {
                    if (!empty($criteria['close_lelang'][0])) {
                        return $query->where('auction_domain.close_date', 'like', '%' . $criteria['close_lelang'][0] . '%');
                    } elseif (!empty($criteria['close_lelang'][1])) {
                        return $query->where('auction_domain.close_date', 'like', '%' . $criteria['close_lelang'][1] . '%');
                    } else {
                        return $query->whereRaw('auction_domain.close_date LIKE "%"');
                    }
                }
            })
            ->groupBy('sell_domain.domain')
            ->get();
    
        return $data;
    }
    
    public function DBInsertUniqID($domain, $uniqid)
    {
        $isexist = DB::table('sell_domain')->where('domain', $domain)->first();
        if (!boolval($isexist)) {
            return DB::table('sell_domain')->insert(['domain' => $domain, 'clientid' => $this->uid, 'uniqid' => $uniqid, 'status' => 'NEED_VERIFY']);
        } else {
            return DB::table('sell_domain')->where('domain', $domain)->update(['uniqid' => $uniqid]);
        }
    }

    public function DBsetPrice($domain, $price, $type = null)
    {
        if ($type == null) {
            $type = DB::table('sell_domain')->where('domain', $domain)->first()->type;
        }

        DB::table('auction_domain')->where('domain', $domain)->update(['price' => $price]);

        if (boolval($type)) {
            return DB::table('sell_domain')->where('domain', $domain)->update(['price' => $price, 'type' => $type]);
        }
        return DB::table('sell_domain')->where('domain', $domain)->update(['price' => $price]);
    }

    public function DBsetEPP($domain, $epp)
    {
        return DB::table('sell_domain')->where('domain', $domain)->update(['epp' => $epp]);
    }

    public function DBsetBank($name, $rekening, $atasnama)
    {
        $isexist_bank = DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'bank')->first();
        if (boolval($isexist_bank)) {
            DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'bank')->update(['value' => $name]);
        } else {
            DB::table('sell_domain_config')->insert([
                'clientid' => $this->uid,
                'field' => 'bank',
                'value' => $name,
            ]);
        }

        $isexist_rekening = DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'rekening')->first();
        if (boolval($isexist_rekening)) {
            DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'rekening')->update(['value' => $rekening]);
        } else {
            DB::table('sell_domain_config')->insert([
                'clientid' => $this->uid,
                'field' => 'rekening',
                'value' => $rekening,
            ]);
        }

        $isexist_an = DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'atasnama')->first();
        if (boolval($isexist_an)) {
            DB::table('sell_domain_config')->where('clientid', $this->uid)->where('field', 'atasnama')->update(['value' => $atasnama]);
        } else {
            DB::table('sell_domain_config')->insert([
                'clientid' => $this->uid,
                'field' => 'atasnama',
                'value' => $atasnama,
            ]);
        }
    }

    public function DBInsertToLelang($domain)
    {
        $cekexist = DB::table('auction_domain')->where('domain', $domain)->first();
        if (!boolval($cekexist)) {
            $single_data = DB::table('sell_domain')->where('domain', $domain)->first();
            if ($single_data->type == 'FIX_PRICE') {
                $param = [
                    'domain' => $domain,
                    'price' => $single_data->price,
                    'status' => 'SELL_DOMAIN',
                    'owner' => $this->uid,
                ];
            } else {
                $param = [
                    'domain' => $domain,
                    'price' => $single_data->price,
                    'status' => 'OPEN_LELANG',
                    'owner' => $this->uid,
                ];
            }
            DB::table('auction_domain')->insert($param);
        }
    }

    public function generateUniqID()
    {
        return uniqid() . '-' . uniqid();
    }

    public function getUniqID($domain)
    {
        if (boolval($domain)) {
            $sellDomain = DB::table('sell_domain')->where('domain', $domain)->first();
            if ($sellDomain) {
                return $sellDomain->uniqid; 
            }
        }
        
        return null;
    }

    public function requestWHMCS($domain)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://portal.qwords.com/order/apis/find2.php?domain=' . $domain,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            return $err;
        } else {
            return json_decode($response);
        }
    }

    public function generateTokenHTMLFile($domain)
    {
        $uniqid = $this->generateUniqID();
        $uniq_file = 'portal' . $uniqid . '.html';
        $content = 'site-verification: ' . $uniq_file;
        $byte = file_put_contents($this->html_dir . $uniq_file, $content);
        if (boolval($byte)) {
            $this->DBInsertUniqID($domain, $uniqid);
            return $this->module_uri . 'html/' . $uniq_file;
        }
        return 'Can"t write file';
    }

    public function getTokenNameserver($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return null;
        }
        $content = 'site-verification: ' . $uniqid;
        return $content;
    }

    public function getTokenHTML($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return null;
        }
        return 'portal' . $uniqid . '.html';
    }

    public function verifyTokenHTML($domain)
    {
        $uniqid = $this->getUniqID($domain);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://$domain/portal$uniqid.html");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($ch);

        $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_status_code !== 200) {
            curl_setopt($ch, CURLOPT_URL, "https://$domain/portal$uniqid.html");
            $output = curl_exec($ch);
        }
        curl_close($ch);
        return $output == "site-verification: portal$uniqid.html";
    }

    public function verifyTokenDns($domain)
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

    public function verifyTokenNameserver($domain)
    {
        $uniqid = $this->getUniqID($domain);
        if (!boolval($uniqid)) {
            return false;
        }

        $result = $this->requestWHMCS($domain);
        $nameserver1 = false;
        $nameserver2 = false;

        if ($result) {
            if (stripos($result->respon->whois, 'ns1.qwords.io') !== false) {
                $nameserver1 = true;
            }

            if (stripos($result->respon->whois, 'ns2.qwords.io') !== false) {
                $nameserver2 = true;
            }
        }

        return $nameserver1 && $nameserver2;
    }

    public function validateDomain($domain)
    {
        $clientid = $this->uid;
        $result = DB::table('sell_domain')
            ->where('clientid', $clientid)
            ->where('domain', $domain)
            ->first();
        return boolval($result);
    }

    public function getAdminPriceIDR($price)
    {
        $price_tier1 = [16, 10000];
        $price_tier2 = [10001, 25000];
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

    public function notifGeneral($clientid, $subject, $msg)
    {
        $systemHelper = new SystemHelper();
        $postData = [
            'id' => $clientid,
            'customtype' => 'general',
            'customsubject' => $subject,
            'custommessage' => $msg,
        ];
        $results = $systemHelper->SendEmail($postData);
        return $results;
    }

    public function cekSuggestionSellDomain()
    {
        $current_time = now();
        $datas = DB::table('sell_domain_suggestion')
            ->where('start_time', '<=', $current_time)
            ->where('end_time', '>=', $current_time)
            ->get();
        return $datas;
    }

    public function insertSuggestionDomain($domain, $status)
    {
        $exist = DB::table('sell_domain_suggestion')
            ->where('domain', $domain)
            ->first();

        if (!boolval($exist)) {
            $current_time = now();
            $end_time = $current_time->copy()->addDays(7);

            $datas = DB::table('sell_domain_suggestion')
                ->insert([
                    'domain' => $domain,
                    'start_time' => $current_time,
                    'end_time' => $end_time,
                    'status' => $status
                ]);

            $whmcsHelper = new WHMCS_Helper();
            $postData = [
                'userid' => $this->uid,
                'status' => 'Unpaid',
                'paymentmethod' => 'mailin',
                'taxrate' => $this->ppn,
                'description' => "Biaya tambahan suggestion $domain ",
                'type' => 'Domain',
                'amount' => '500000',
                'taxed' => '1',
                'notes' => 'sell_domain'
            ];
            $results = $whmcsHelper->CreateInvoice($postData);
            return $results;
        } else {
            $datas = DB::table('sell_domain_suggestion')
                ->where('domain', $domain)
                ->update(['status' => $status]);
            $datas = "Domain already inserted and updated to $status";
        }

        return [
            'query_result' => $datas
        ];
    }

    public function calculateDanaDicairkan($invoiceid, $clientid)
    {
        $whmcsHelper = new WHMCS_Helper();
        $postData = ['invoiceid' => $invoiceid];
        $results = $whmcsHelper->GetInvoice($postData);

        $foundItem = null;
        foreach ($results['items']['item'] as $item) {
            if (isset($item['description']) && (stripos($item['description'], "Biaya Sell Domain") !== false || stripos($item['description'], "Biaya BID Jual Domain") !== false)) {
                $foundItem = $item;
                break;
            }
        }

        if ($foundItem !== null) {
            $amount = floatval($foundItem['amount']);
        } else {
            echo "Item not found with description 'biaya domain'";
            die;
        }

        $adminprice = $this->getAdminPriceIDR($amount);

        $bank = DB::table('sell_domain_config')
            ->where('clientid', $clientid)
            ->where('field', 'bank')
            ->first();
        $bank = $bank->value;

        $fee = in_array($bank, ['bca', 'mandiri']) ? 0 : 6500;

        if ($adminprice['price'] < 225000) {
            $adminprice['price'] = 225000;
        }

        return [
            'amount' => $amount,
            'adminprice' => $adminprice['price'],
            'withdraw' => $amount - $adminprice['price'] - $fee,
            'adminpersen' => $adminprice['percent'],
            'feebank' => $fee,
        ];
    }

    public function getDanaYangBisaDicairkan($price, $client_id)
    {
        $amount = floatval($price);
        $adminprice = $this->getAdminPriceIDR($amount);

        $bank = DB::table('sell_domain_config')
            ->where('clientid', $client_id)
            ->where('field', 'bank')
            ->first();

        if (!$bank) {
            return [
                'error' => 'Bank configuration not found for this client.'
            ];
        }

        $bank_value = $bank->value;

        $bank_not_set = !boolval($bank_value);
        $fee = in_array($bank_value, ['bca', 'mandiri']) ? 0 : 6500;

        if ($adminprice['price'] < 225000) {
            $adminprice['price'] = 225000;
        }

        return [
            'amount' => $price,
            'adminprice' => $adminprice['price'],
            'withdraw_no_fee' => $amount - $adminprice['price'],
            'adminpersen' => $adminprice['percent'],
            'feebank' => $fee,
            'bank_not_set' => $bank_not_set
        ];
    }

    public function openTicket($clientid, $subject, $msg, $deptid = 1)
    {
        $support = new WHMCS_Helper();
        $postData = [
            'deptid' => $deptid,
            'subject' => $subject,
            'message' => $msg,
            'clientid' => $clientid,
            'priority' => 'Medium',
            'admin' => true,
            'markdown' => true,
        ];

        $results = $support->openTicket($postData);
        return $results;
    }
}
