<?php

namespace App\Http\Controllers\Client;

use App\Helpers\Domains;
use App\Helpers\Format;
use App\Helpers\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\Sslstatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\FileUploader;
use App\Models\Clientsfile;
use App\Helpers\LogActivity;
use App\Helpers\SystemHelper;
use App\Helpers\WHMCS_Helper;
use App\Http\Controllers\Client\_AuctionController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Helpers\DNSManagerHelper;
use App\Http\Controllers\API\Domains\DomainsController as DomainsDomainsController;

class DomainsController extends Controller
{

    // protected $dnsManager;

    // public function __construct()
    // {
    //     $this->dnsManager = new DNSManagerHelper();
    // }

    // route get to mydomains
    public function Domains_MyDomains()
    {
        $auth = Auth::user();
        $userid = $auth->id;

        $getDomains = Domain::where("userid", $userid)->orderBy("id", "DESC")->get();
        $getUserContacts = Contact::where('userid', $auth->id)->orderBy('id', 'desc')->get();
        $domainsCount = $getDomains->count();

        return view('pages.domain.mydomains.index', ['getDomain' => $getDomains, 'contactList' => $getUserContacts, 'domainsCount' => $domainsCount]);
    }
    // route get to mydomains

    public function Domains_LelangDomainAction(Request $request)
    {
        $auth = Auth::user();
        $clientid = $auth->id;

        // Ambil action dari request
        $action = $request->input('action');
        $domain = $request->input('domain');

        switch ($action) {
            case 'bid':
                $auction = new _AuctionController();

                $formtoken = $request->post('bid_token');
                $bid_value = $request->post('bid_value');
                $last_price_form = $request->post('last_price_form');
                $anon_email = $request->post('anon_email');
                $currentDeposit = $auction->getDeposit();
                // $currentDeposit = 100000000;

                // Proses CSRF token yang tidak cocok
                if ($formtoken !== session('bid_token')) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'CSRF Token tidak sama'
                    ]);
                }
                // Validasi nilai bid terlalu besar
                $resultMaxBid = $auction->validasiBidTerlaluBesar($domain, $bid_value);            // Validasi bid tidak boleh kosong
                if (!boolval($resultMaxBid)) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Harga bid tidak boleh kosong'
                    ]);
                }
                // Validasi nilai bid harus di bawah 5 juta
                if ($bid_value > 5000000) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Maksimal bid 5 juta'
                    ]);
                }
                // Cek minimal bid
                $resultMinBid = $auction->cekMinimalBid($domain, $bid_value);
                if (!boolval($resultMinBid)) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Jumlah minimal untuk bid pertama >= 100'
                    ]);
                }
                // Cek jika user telah bid 2 kali berturut-turut
                $resultSeqBid = $auction->maxBidBerurutan($domain, $bid_value);
                if ($resultSeqBid >= 2) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Maksimal bid 2x'
                    ]);
                }
                //Check apakah waktu lelang telah berakhir
                $detail = $auction->getDetail($domain);
                if ($detail && $detail->close_date) {
                    $endtime = strtotime($detail->close_date);
                    if ($endtime < time()) {
                        return redirect()->back()->with([
                            'alert-type' => 'danger',
                            'alert-message' => 'Lelang telah berakhir'
                        ]);
                    }
                } else {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Detail lelang tidak ditemukan'
                    ]);
                }
                // Cek Deposit akun yang login
                $removedCredit = $auction->getCreditRemoved($clientid, $domain, $bid_value);
                if ($currentDeposit < $removedCredit) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Saldo Deposit tidak mencukupi untuk melakukan bid'
                    ]);;
                }
                // Cek apakah user sedang bid di domain miliknya sendiri
                $isDomainSendiri = DB::table('auction_domain')
                    ->where('domain', $domain)
                    ->where('owner', $clientid)
                    ->first();
                if ($isDomainSendiri) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Tidak bisa bid ke domain sendiri'
                    ]);;
                }
                // Cek jika user diizinkan untuk melihat atau bid domain ini
                $resultAllow = $auction->checkUserAllowedViewAuction($domain, $clientid);
                if (!$resultAllow) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Harus pesan dulu minimal 1 domain'
                    ]);
                }

                // dd($domain);
                // Cek apakah harga terakhir sesuai dengan form
                $lastprice = $auction->getLastPrice($domain);
                $last_price_form = $request->post('last_price_form');

                if ($lastprice == $last_price_form) {
                    $sukses = $auction->addBid($domain, $bid_value, $anon_email);
                    // Jika sukses kirim email ke LastClient 
                    if ($sukses) {
                        $last_client_bid = 0;
                        // $this->sendEmailToLastClient($last_client_bid, $domain, $bid_value);
                    }
                    // Tambah waktu jika lelang berada di 1 jam terakhir
                    $isLastHour = $auction->apakahAdaDi1JamTerakhir($detail->close_date);
                    if ($isLastHour) {
                        $maxtry = $auction->getMaxtry($domain);

                        if ($maxtry < 5) {
                            $newtime = $auction->tambahWaktuLelang($maxtry, $domain);

                            if ($newtime != $detail->close_date) {
                                $auction->db_perpanjangLelang($newtime, $domain);
                            }
                        }
                    }
                    // Jika dari sell_domain
                    $from_sell_domain = DB::table('auction_domain')
                        ->where('domain', $domain)
                        ->where('owner', '<>', null)
                        ->first();
                    if ($from_sell_domain) {
                        // $this->sendEmailToLastClientFromSellDomain($from_sell_domain, $domain, $bid_value);
                    }
                    return redirect()->back()->with([
                        'alert-type' => 'success',
                        'alert-message' => 'Bid sukses ditambahkan'
                    ]);
                } else {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Pengguna lain telah memasukan harga baru. Silahkan masukan kembali bid terbaru'
                    ]);
                }
            break;

            case 'save_setting':
                $notif = $request->input('notif') == 'on' ? 1 : 0;
                $email_asterix = $request->post('email_asterix') == 'on' ? 1 : 0;

                try {
                    DB::beginTransaction();
                    $updated = DB::table('auction_domain_setting')->updateOrInsert(
                    ['client_id' => $clientid],
                        ['is_hidden' => $email_asterix, 'notif_domain' => $notif]
                    );
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal update setting:' . $e->getMessage()
                    ]);
                }

                $url = route('pages.domain.lelangdomains.index', ['page' => 'setting']);
                return redirect($url)->with([
                    'alert-type' => 'success',
                    'alert-message' => 'Sukses menambahkan notifikasi lelang'
                ]);
            break;

            default:
                $this->showRouteNotFound();
                return;
            break;
        }
    }

    // route get to lelangdomains
    public function Domains_LelangDomain()
    {
        $auction = new _AuctionController();
        $auth = Auth::user();
        $clientid = $auth->id;

        $datas = [
            'public_domain' => [],
            'list'          => [],
            'detail'        => [],
            'history'       => [],
            'total_order'   => 0,
            'bid_token'     => '',
            'waktu'         => '',
            'tipe'          => '',
        ];
        $clientarea_page['vars'] = $datas;

        $page = request()->get('page');
        $domain = request()->get('domain');

        $table = $auction->getData($domain);
        $detail = $auction->getDetail($clientid);
        $total_order = $auction->getTotalOrder($domain, $clientid);
        $history = $auction->getHistory($domain);

        $history_obfuscate = $auction->obfuscate_email($history);

        if (empty($page) && empty($domain)) {
            $waktu = request()->get('waktu');
            $tipe = request()->get('tipe');
            if (!$tipe) {
                $waktu = 'waktu-terdekat';
                $tipe = 'normal';
            }
            
            if ($waktu && $tipe) {
                // Tentukan arah pengurutan berdasarkan waktu
                $orderDirection = 'ASC';
                if ($waktu == 'waktu-terlama') {
                    $orderDirection = 'DESC';
                }
                // Tentukan filter tipe untuk `public_domain`
                if ($tipe == 'backorder') {
                    $tipe = 'backorder';
                } elseif ($tipe == 'client') {
                    $tipe = 'client';
                } else {
                    $tipe = 'normal';
                }

                $public_domain = $auction->getAuctionData($clientid, $waktu, $tipe);
                // dd($public_domain);
                // Query umum untuk `sell_domain`
                $sell_domain_query = DB::table('auction_domain')
                    ->select(['auction_domain.*'])
                    ->join('sell_domain', 'sell_domain.domain', '=', 'auction_domain.domain')
                    ->where('enabled', 1)
                    ->where('sell_domain.price', '>', 249999)
                    ->where('auction_domain.status', 'SELL_DOMAIN')
                    ->groupBy('auction_domain.domain')
                    ->orderByRaw("auction_domain.close_date IS NULL, auction_domain.close_date $orderDirection");

                // Tambahkan filter berdasarkan tipe pada `sell_domain_query`
                if ($tipe == 'backorder') {
                    $sell_domain_query->whereNull('owner');
                } elseif ($tipe == 'client') {
                    $sell_domain_query->whereNotNull('owner');
                }

                // Eksekusi query `sell_domain` menjadi array
                $sell_domain = $sell_domain_query->get()->toArray();

                // Update last_price dan status dari sell_domain
                foreach ($sell_domain as $sdata) {
                    $sdata->last_price = $sdata->price;
                    $sdata->status = $sdata->status;
                }
            }

            if ($sell_domain) {
                $public_domain = array_merge($public_domain, $sell_domain);
            }

            if (!boolval($domain) && !in_array($page, ['my_auction', 'setting'])) {
                $public_domain = array_map(function ($val) {
                    $auction = new _AuctionController();

                    if ($val->owner == NULL) {
                        $total_order = $auction->getPriceDomain($val->domain);
                        $val->price_domain = $total_order;
                    }
                    return $val;
                }, $public_domain);

                $systemHelper = new SystemHelper();
                $response = $systemHelper->GetPaymentMethods();

                if ($response['result'] === 'success') {
                    $payment_methods = $response['paymentmethods']['paymentmethod'];
                } else {
                    $payment_methods = [];
                }

                $datas = [
                    'public_domain' => $public_domain,
                    'list'          => $table,
                    'detail'        => $detail,
                    'history'       => $history_obfuscate,
                    'total_order'   => $total_order,
                    'bid_token'     => '',
                    'waktu'         => $waktu,
                    'tipe'          => $tipe,
                    'payment_methods' => $payment_methods
                ];
                $clientarea_page['vars'] = $datas;
            }
            // dd($clientarea_page);
            return view('pages.domain.lelangdomains.index', $clientarea_page['vars']);
        }

        if (!boolval($domain)) {
            $domain = request()->post('domain');
        }
        switch ($page) {
            case 'detail':
                if ($page == 'detail' && !$domain) {
                    $auction->showRouteNotFound();
                    return;
                }

                // Menghasilkan token untuk bid
                $token = bin2hex(random_bytes(32));
                session(['bid_token' => $token]);

                $total_hargadomain = $auction->getTotalOrder($domain, $clientid);
                $detail = $auction->getDetail($domain);

                // Memeriksa apakah pengguna diizinkan untuk melihat lelang
                if (!$auction->checkUserAllowedViewAuction($domain, $clientid)) {
                    $clientarea_page['vars']['hide_form'] = true;
                }
                // Memastikan bahwa detail tidak kosong
                if (!$detail) {
                    return redirect()->back()->with('message_error', 'Domain tidak ditemukan.');
                }

                // Mengatur variabel yang diperlukan untuk template
                $clientarea_page['vars']['bid_token'] = $token;
                $clientarea_page['vars']['total_hargadomain'] = $total_hargadomain;
                $clientarea_page['vars']['detail'] = $detail;
                $clientarea_page['vars']['detail']->maxtry = 1;

                // Cek apakah ini adalah bid pertama kali
                $firsttime = DB::table('auction_domain_history')
                    ->where('domain', $domain)
                    ->where('status_deposit', 'HOLD')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                $is_firsttime_bid = $firsttime && $firsttime->bid_price > 0 ? false : true;
                $clientarea_page['vars']['is_firsttime_bid'] = $is_firsttime_bid;
                $clientarea_page['vars']['min_bid_value'] = $is_firsttime_bid ? 50 : 50;

                // dd($clientarea_page);
                return view('pages.domain.lelangdomains.detail', $clientarea_page['vars']);

            break;

            case 'history':
                if ($page == 'history' && !$domain) {
                    $auction->showRouteNotFound();
                    return;
                }
                
                $detail = $auction->getDetail($domain);
                $history = $auction->getHistory($domain);
                $all_history = $auction->GetAllHistory($domain, $clientid);

                if ($detail) {
                    $detail->maxtry = 1;
                }

                // Menyimpan data history dan detail untuk digunakan di view
                $clientarea_page['vars']['history'] = $history;
                $clientarea_page['vars']['all_history'] = $all_history; //untuk admin
                $clientarea_page['vars']['detail'] = $detail;
                // dd($clientarea_page);
                return view('pages.domain.lelangdomains.history', $clientarea_page['vars']);

            break;

            case 'setting':
                $setting = DB::table('auction_domain_setting')->where('client_id', $clientid)->first();

                if ($setting) {
                    $clientarea_page['vars']['setting_notif'] = $setting->notif_domain;
                    $clientarea_page['vars']['setting_email_asterix'] = $setting->is_hidden;
                } else {
                    $clientarea_page['vars']['setting_notif'] = 0;  
                    $clientarea_page['vars']['setting_email_asterix'] = 0;
                }

                return view('pages.domain.lelangdomains.setting', $clientarea_page['vars']);
            break;

            case 'buy_domain_lelang':
                if ($page == 'buy_domain_lelang' && !$domain) {
                    $auction->showRouteNotFound();
                    return;
                }

                $domain = request()->get('domain');
                $res = $auction->buyDomainIDRLelang($domain, $clientid);

                if ($res['status'] == 200) {
                    $url = route('pages.domain.lelangdomains.index', ['page' => 'detail', 'domain' => $domain]);
                    return redirect($url)->with([
                        'alert-type' => 'success',
                        'alert-message' => 'Sukses mengikuti lelang'
                    ]);
                } else {
                    $url = route('pages.domain.lelangdomains.index');
                    return redirect($url)->with([
                        'alert-type' => 'danger',
                        'alert-message' => $res['message']
                    ]);
                }
            break;

            case 'buy_domain':
                if (!$domain) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Domain tidak valid'
                    ]);
                }
                
                $last_price = request()->get('last_price');
                if(!$last_price){
                    $last_price = 0;
                    return redirect()->back()->with('message_error', 'last_price tidak ditemukan.');
                }

                $auction = new _AuctionController();
                $result = $auction->buyDomainIDR($domain, $clientid, $last_price);

                if ($result['result'] == 'error') {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal membuat invoice: ' . $result['message']
                    ]);
                }

                return redirect()->route('pages.services.mydomains.viewinvoiceweb', $result['invoiceid']);

            break;

            case 'my_auction':
                // Query untuk mengambil data dari auction_domain dan auction_domain_history
                $query = DB::table('auction_domain')
                    ->join('auction_domain_history', 'auction_domain_history.domain', '=', 'auction_domain.domain')
                    ->select('auction_domain.*', DB::raw('MAX(auction_domain_history.last_price) as price_last'))
                    ->where('auction_domain_history.client_id', $clientid)
                    ->where('auction_domain.status', '!=', 'ARCHIEVED')
                    ->groupBy('auction_domain.domain');

                // Tampilkan SQL Query dan Bindings (untuk debugging)
                $sql = $query->toSql();
                $bindings = $query->getBindings();
                foreach ($bindings as $binding) {
                    $sql = preg_replace('/\?/', '"' . addslashes($binding) . '"', $sql, 1);
                }

                // dd($sql);
                // Tambahkan logika tambahan untuk penyesuaian list
                $list = $query->get()->toArray();
                $list = array_map(function ($val) use ($clientid) {
                    $auction = new _AuctionController();
                    if (boolval($val->owner)) {
                        // Lakukan sesuatu jika val->owner ada
                    } else {
                        $total_order = $auction->getTotalOrder($val->domain, $clientid);
                        $val->price_last += $total_order;
                    }
                    return $val;
                }, $list);

                $clientarea_page['vars']['list'] = $list;
                // dd($clientarea_page);
                return view('pages.domain.lelangdomains.my_auction', $clientarea_page['vars']);

            break;

            case 'list':
                if ($page == 'list' && !$domain) {
                    $auction->showRouteNotFound();
                    return;
                }

                $public_domain = [];
                $waktu = [];
                $tipe = [];
                $datas = [
                    'public_domain' => $public_domain,
                    'list'          => $table,
                    'detail'        => $detail,
                    'history'       => $history_obfuscate,
                    'total_order'   => $total_order,
                    'bid_token'     => '',
                    'waktu'         => $waktu,
                    'tipe'          => $tipe,
                ];
                $clientarea_page['vars'] = $datas;
                // dd($clientarea_page['vars']);
                return view('pages.domain.lelangdomains.list', $clientarea_page['vars']);

            break;

            default:
                $auction->showRouteNotFound();
                return;
            break;
        }

        // return view('pages.domain.lelangdomains.index', []);
    }
    //route get to lelangdomains


    public function Domains_SellDomain()
    {
        $auth = Auth::user();
        $clientid = $auth->id;

        $sellDomain = new _SellDomainController();
        $rentDomain = new _SellDomainLeaseController();

        $page = request()->get('page');
        $allDomain = $sellDomain->getDomainAll();
        $allRentDomain = $rentDomain->getRentDomainAll();

        // $criteria = [
        //     "domain" => request()->get('domain'),
        //     "status" => request()->get('status'),
        //     "type" => request()->get('type'),
        //     "open_lelang" => request()->get('open_lelang'),
        //     "close_lelang" => request()->get('close_lelang'),
        // ];

        $clientarea_page = [
            // 'pagetitle' => 'Jual Domain - PT Qwords Company International',
            // 'breadcrumb' => [
            //     'index.php?m=sell_domain' => 'Jual Domain'
            // ],
            // 'templatefile' => 'tpl/domains',
            // 'requirelogin' => true, 
            // 'forcessl' => false, 
            'vars' => [],
        ];

        if (
            !empty($criteria['domain']) ||  
            !empty($criteria['status']) ||
            !empty($criteria['type']) ||
            !empty($criteria['open_lelang']) ||
            !empty($criteria['close_lelang'])
        ) {
            // $filter = $sellDomain->filterDomain($criteria);
            // $domains = $filter;
            // dump($domains);
        }

        if ($page == 'rent') {
            return view('pages.domain.selldomains.rent', [
                'domain_rents_all' => $rentDomain,
            ]);
        }

        if (empty($page)) {
            $user = Auth::user();
            $adminIds = [1];
        
            if (in_array($user->id, $adminIds)) {
                return view('pages.domain.selldomains.index', [
                    'domains' => $allDomain,
                    'domain_rents' => $allRentDomain,
                ]);
            } else {
                return redirect()->route('pages.domain.selldomains.index', ['page' => 'list']);
            }
        }

        switch ($page) {
            case 'list':
                $domains = $sellDomain->getMySellDomains();
                $domain_rents = DB::table('rent_domain')->where('clientid', $clientid)->get();
                
                foreach ($domains as &$domain) {
                    $result = $sellDomain->requestWHMCS($domain->domain);
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
                
                    $domain->nameserver = ($nameserver1 && $nameserver2) ? true : false;
                }
                
                $clientarea_page['vars']['domains'] = $domains;
                $clientarea_page['vars']['domain_rents'] = $domain_rents;
                
                return view('pages.domain.selldomains.index', $clientarea_page['vars']);
            break;

            case 'insert':
                $domain = request()->get('domain');
                $qwordsdomains = $sellDomain->getDomains();
                $section = request()->get('section');

                $suggest_price = 250000;
                if($domain){
                    $sell_domain = DB::table('sell_domain')->where('domain', $domain)->first();
                    if ($sell_domain){
                        $suggest_price = intval($sell_domain->price);
                    } else {
                        $AuctionHelper = new _AuctionController();
                        $suggest_price = intval($AuctionHelper->getPriceDomain($domain));
                    }
                }
                
                $nameserver_uniq = $sellDomain->getTokenNameserver($domain);
                $htmlfile = $sellDomain->getTokenHTML($domain);
                
                if (!boolval($nameserver_uniq)){
                    $nameserver_uniq = $rentDomain->getTokenNameserver($domain);
                }
                
                if (!boolval($htmlfile)){
                    $htmlfile = $rentDomain->getTokenHTML($domain);
                }
                
                $clientarea_page['vars']['htmlfile'] = $htmlfile;
                $clientarea_page['vars']['suggest_price'] = $suggest_price;
                $clientarea_page['vars']['domain'] = $domain;
                $clientarea_page['vars']['nameserver_uniq'] = $nameserver_uniq;
                $clientarea_page['vars']['qwordsdomains'] = $qwordsdomains;
                $clientarea_page['vars']['section'] = $section;
                $clientarea_page['vars']['clientid'] = $clientid;
                // $token = bin2hex(random_bytes(32));
                // $_SESSION['sell_token'] = $token;
                // $clientarea_page['vars']['sell_token'] = $token;
                
                return view('pages.domain.selldomains.insert', $clientarea_page['vars']);
            break;

            case 'edit':
                $domain = request()->get('domain');
                $disabled_lelang = request()->get('disabled_lelang');
                $price_label = request()->get('price_label');
                $price_kontan = request()->get('price_kontan');
                $price_sewa = request()->get('price_sewa');
                $disabled_lelang = request()->get('disabled_lelang');

                $clientarea_page['vars']['domain'] = $domain;
                // $clientarea_page['vars']['price'] = $sellDomain->getPrice($domain);
                $price_sell = $sellDomain->getPrice($domain);
                
                $clientarea_page['vars']['section'] = '';
                $clientarea_page['vars']['suggest_price'] = $suggest_price ?? 250000;
                $clientarea_page['vars']['price_awal'] = $price_sell['awal'];
                $clientarea_page['vars']['price_kontan'] = $price_sell['kontan'];
                $clientarea_page['vars']['price_sewa'] = $price_sell['sewa'];

                
                $domains_sell = DB::table('sell_domain')->where('domain', $domain)->get();
                $disabledLelang = count($domains_sell) == 1;

                if ($disabledLelang){
                    $clientarea_page['vars']['is_disabled_lelang'] = true;
                } else {
                    $clientarea_page['vars']['is_disabled_lelang'] = false;
                }

                return view('pages.domain.selldomains.insert', $clientarea_page['vars']);
            break;

            case 'setting':
                $clientid = Auth::user()->id;
                $databank = $sellDomain->getBankClient($clientid);
                if (boolval($databank)) {
                    $clientarea_page['vars']['bank'] = $databank->bank; 
                    $clientarea_page['vars']['rekening'] = $databank->rekening;
                    $clientarea_page['vars']['atasnama'] = $databank->atasnama;
                } else {
                    $clientarea_page['vars']['bank'] = '';
                    $clientarea_page['vars']['rekening'] = '';
                    $clientarea_page['vars']['atasnama'] = '';
                }

                // $token = bin2hex(random_bytes(32));
                // $_SESSION['sell_token'] = $token;
                // $clientarea_page['vars']['sell_token'] = $token;                
                return view('pages.domain.selldomains.setting', $clientarea_page['vars']);
            break;

            case 'epp':
                // $token = bin2hex(random_bytes(32));
                // $_SESSION['sell_token'] = $token;
                // $clientarea_page['vars']['sell_token'] = $token;
                $domain = request()->get('domain');
                $clientarea_page['vars']['domain'] = $domain;
                return view('pages.domain.selldomains.epp', $clientarea_page['vars']);
            break;

            case 'rent_all':
                $domain = request()->get('domain');
     
                if (boolval($domain)) {
                    $domain_rents_all = DB::table('rent_domain')
                        ->where('status', 'VERIFIED')
                        ->where('domain', $domain)
                        ->get()->toArray();
                } else {
                    $domain_rents_all = DB::table('rent_domain')
                        ->where('status', 'VERIFIED')
                        ->get()->toArray();
                }
                
          
                $clientarea_page['vars']['domain_rents_all'] = $domain_rents_all;
                return view('pages.domain.selldomains.rent', $clientarea_page['vars']);
            break;            

            case 'my_rent':
                $clientid = Auth::user()->id;
                $domain_rents_transaction = DB::table('rent_domain_transaction')
                    ->join('rent_domain', 'rent_domain_transaction.domain','=', 'rent_domain.domain')
                    ->where('rent_domain_transaction.status', 'ACTIVE')
                    ->where('rent_domain_transaction.rental', $clientid)
                    ->groupBy('rent_domain_transaction.domain')
                    ->get();
                
                $clientarea_page['vars']['domain_rents_transaction'] = $domain_rents_transaction;
                return view('pages.domain.selldomains.my_rent', $clientarea_page['vars']);
            break;

            default:
                $auction = new _AuctionController();
                $auction->showRouteNotFound();
                return;
            break;

        }   
    }

    public function Domains_SellDomainAction(Request $request)
    {
        $sellDomain = new _SellDomainController();
        $rentDomain = new _SellDomainLeaseController();

        $action = $request->get('action');
        $domain = $request->get('domain');

        $page = $request->get('page');
        if(!empty($page)){
            $auction = new _AuctionController();
            $auction->showRouteNotFound();
            return;

        }

        switch ($action) {
            case 'sell':
                $initial_price = $request->input('price');
                $type = $request->input('type');
                $is_suggest = $request->input('is_suggest');
                $is_disabled_lelang = $request->input('disabled_lelang');

                $formtoken = $request->input('sell_token');
                $domain = $request->input('domain');
                
                $select_domain = DB::table('sell_domain')->where('domain', $domain)->first();
                if ($select_domain) {
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', 'Domain sudah terdaftar');
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'insert',
                    ]);
                }

                $price_awal = $request->input('price_awal');
                $price_kontan = $request->input('price_kontan');
                $price_rent = $request->input('price_sewa');
                // $uid = Session::get('uid');
                $uid = Auth::user()->id;
                // Validation
                if ($formtoken !== Session::get('sell_token')) {
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', 'Token tidak cocok');
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'insert',
                    ]);
                }

                // WHOIS validation
                // $whois = $sellDomain->requestWHMCS($domain);
                $domainHelper = new Domains();
                $whois = $domainHelper->domainWhois($domain);
                if (!$whois) {
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', 'Status domain masih tersedia');
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'insert',
                    ]);
                }

                $uniqid = uniqid() . '-' . uniqid();

                // Insert Lelang
                if ($price_rent > 0) {
                    DB::table('rent_domain')->insert([
                        'clientid' => $uid,
                        'domain' => $domain,
                        'price' => $price_rent,
                        'status' => 'NEED_VERIFY'
                    ]);

                    DB::table('domain_uniqid')->insert([
                        'domain' => $domain,
                        'uniqid' => $uniqid,
                    ]); 
                }

                if ($price_awal > 240000 && !$is_disabled_lelang) {
                    DB::table('sell_domain')->insert([
                        'domain' => $domain,
                        'clientid' => $uid,
                        'uniqid' => $uniqid,
                        'price' => $price_awal,
                        'type' => 'AUCTION_PRICE',
                        'status' => 'NEED_VERIFY'
                    ]);
                } else {
                    DB::table('sell_domain')
                        ->where('domain', $domain)
                        ->where('clientid', $uid)
                        ->where('type', 'AUCTION_PRICE')
                        ->limit(1)
                        ->delete();
                }
                
                if ($price_kontan > 240000) {
                    DB::table('sell_domain')->insert([
                        'domain' => $domain,
                        'clientid' => $uid,
                        'uniqid' => $uniqid,
                        'price' => $price_kontan,
                        'type' => 'FIX_PRICE',
                        'status' => 'NEED_VERIFY'
                    ]);
                }

                // Send notification emails
                if ($uid !== 59756) {
                    $postData = [
                        'id' => '27851',
                        'customtype' => 'general',
                        'customsubject' => 'Ada Sell domain baru ' . $domain . ' masuk katalog',
                        'custommessage' => '<p style="margin-top:1em;margin-bottom:1em;">Ada domain baru di Sell DOmain, silakan cek di halaman admin <a href="https://portal.qwords.com/qwadmin/addonmodules.php?module=sell_domain">disini</a>.</p>',
                        'customvars' => base64_encode(serialize(['domain' => $domain])),
                    ];
                    $postData['id'] = '59316';
                }
                $systemHelper = new SystemHelper();
                $systemHelper->SendEmail($postData);

                // Domain verification and further actions
                if ($sellDomain->isDomainQword($domain)) {
                    DB::table('sell_domain')
                        ->where('domain', $domain)
                        ->take(2)
                        ->update(['uniqid' => 'qwords', 'status' => 'VERIFIED']);
                    DB::table('rent_domain')
                        ->where('domain', $domain)
                        ->take(1)
                        ->update(['status' => 'VERIFIED']);
                $cek_lelang = DB::table('auction_domain')->where('domain', $domain)->first();
                if (!$cek_lelang && $price_awal) {
                    if (!$is_disabled_lelang) {
                        DB::table('auction_domain')->insert([
                            'domain' => $domain,
                            'price' => $price_awal,
                            'status' => 'OPEN_LELANG',
                            'owner' => $uid,
                        ]);
                    }
                }

                if ($price_kontan) {
                    DB::table('auction_domain')->insert([
                        'domain' => $domain,
                        'price' => $price_kontan,
                        'status' => 'SELL_DOMAIN',
                        'owner' => $uid,
                        ]);
                    }
                    return redirect()->route('pages.domain.selldomains.index', ['page' => 'list']);

                } else {
                    $uniq_file = 'portal' . $uniqid . '.html';
                    $content = 'site-verification: ' . $uniq_file;
                    Storage::put('sell_domain/html/' . $uniq_file, $content);
                    $sellDomain->notifGeneral($uid, 'Verifikasi Sell Domain', "<p>Domain $domain belum terverifikasi, segera verifikasi domain supaya anda dapat mulai menjual domain di Qwords</p>");

                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'insert',
                        'section' => 'modal',
                        'domain' => $domain
                    ]);
                }
            break;

            case 'update_note':
                $domain = $request->input('domain');
                $sellerNote = $request->input('seller_note');
                if (!$domain) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Domain tidak ditemukan'
                    ]);
                }

                if(!$sellerNote) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Seller Note tidak ditemukan'
                    ]);
                }

                $sellerNote = htmlspecialchars($sellerNote, ENT_QUOTES, 'UTF-8');
                try {
                    DB::table('sell_domain')->where('domain', $domain)->update(['seller_note' => $sellerNote]);
                } catch (\Exception $e) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal update seller note'
                    ]);
                }
                Session::flash('alert-type', 'success');
                Session::flash('alert-message', 'Berhasil update seller note');
                return redirect()->route('pages.domain.selldomains.index', ['page' => 'list']);
            break;
        
            case 'verify':
                // Validation
                $domain = $request->input('domain');
                $section = $request->input('section');
                $formtoken = $request->input('sell_token');
                if ($formtoken !== Session::get('sell_token')) {
                    return redirect()->route('pages.domain.selldomains.index', ['page' => 'insert'])->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Token tidak cocok'
                    ]);
                }
    
                $type = $request->input('type');
                $domain = $request->input('domain');
                $uid = Auth::user()->id;
                // $uid = Session::get('uid');
                $is_disabled_lelang = $request->input('is_disabled_lelang', false);
    
                $valid = $sellDomain->validateDomain($domain);
                if (!$valid) {
                    // abort(403, 'Domain not allowed');
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Client not allowed to change this domain'
                    ]);
                }
    
                $message = '';
                $message_error = '';
    
                if ($type === 'html') {
                    $result = $sellDomain->verifyTokenHTML($domain);
    
                    if ($result) {
                        $message = 'Sukses verifikasi HTML';
                        DB::table('sell_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
                        DB::table('rent_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
    
                        $auction = DB::table('sell_domain')->where('domain', $domain)->where('type', 'AUCTION_PRICE')->first();
                        $fix = DB::table('sell_domain')->where('domain', $domain)->where('type', 'FIX_PRICE')->first();
    
                        $param_sell = [
                            'domain' => $domain,
                            'price' => $fix->price,
                            'status' => 'SELL_DOMAIN',
                            'owner' => $uid,
                        ];
                        DB::table('auction_domain')->insert($param_sell);
    
                        $cek_lelang = DB::table('auction_domain')->where('domain', $domain)->first();
    
                        if (!$cek_lelang && !$is_disabled_lelang) {
                            $param_auction = [
                                'domain' => $domain,
                                'price' => $auction->price,
                                'status' => 'OPEN_LELANG',
                                'owner' => $uid,
                            ];
                            DB::table('auction_domain')->insert($param_auction);
                        }
                    } else {
                        $message_error = 'HTML tidak terverifikasi';
                        Session::flash('alert-type', 'danger');
                        Session::flash('alert-message', $message_error);
                        return redirect()->route('pages.domain.selldomains.index', [
                            'page' => 'insert',
                            'section' => 'modal',
                            'domain' => $domain,
                        ]);
                    }
                }
    
                if ($type === 'dns') {
                    $result = $sellDomain->verifyTokenDns($domain);
    
                    if ($result) {
                        $message = 'Sukses verifikasi DNS';
                        DB::table('sell_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
                        DB::table('rent_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
    
                        $auction = DB::table('sell_domain')->where('domain', $domain)->where('type', 'AUCTION_PRICE')->first();
                        $fix = DB::table('sell_domain')->where('domain', $domain)->where('type', 'FIX_PRICE')->first();
    
                        $param_sell = [
                            'domain' => $domain,
                            'price' => $fix->price,
                            'status' => 'SELL_DOMAIN',
                            'owner' => $uid,
                        ];
                        DB::table('auction_domain')->insert($param_sell);
    
                        if (!$is_disabled_lelang) {
                            $param_auction = [
                                'domain' => $domain,
                                'price' => $auction->price,
                                'status' => 'OPEN_LELANG',
                                'owner' => $uid,
                            ];
                            DB::table('auction_domain')->insert($param_auction);
                        }
                    } else {
                        $message_error = 'DNS tidak terverifikasi';
                        Session::flash('alert-type', 'danger');
                        Session::flash('alert-message', $message_error);
                        return redirect()->route('pages.domain.selldomains.index', [
                            'page' => 'insert',
                            'section' => 'modal',
                            'domain' => $domain,
                        ]);
                    }
                }
    
                if ($type === 'nameserver') {
                    $result = $sellDomain->verifyTokenNameserver($domain);
    
                    if ($result) {
                        $sellerNote = $request->input('seller_note', '');
                        if (!empty($sellerNote)) {
                            $sellerNote = htmlspecialchars($sellerNote, ENT_QUOTES, 'UTF-8');
                            DB::table('sell_domain')->where('domain', $domain)->update(['seller_note' => $sellerNote]);
                        }
    
                        $response = Http::withoutVerifying()->get("https://qwords.io/parked-domain/parked-domain.php?domain=$domain");
    
                        if ($response->successful()) {
                            $message = 'Sukses verifikasi Nameserver';
                            DB::table('sell_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
                            DB::table('rent_domain')->where('domain', $domain)->update(['status' => 'VERIFIED']);
    
                            $auction = DB::table('sell_domain')->where('domain', $domain)->where('type', 'AUCTION_PRICE')->first();
                            $fix = DB::table('sell_domain')->where('domain', $domain)->where('type', 'FIX_PRICE')->first();
    
                            $param_sell = [
                                'domain' => $domain,
                                'price' => $fix->price,
                                'status' => 'SELL_DOMAIN',
                                'owner' => $uid,
                            ];
                            DB::table('auction_domain')->insert($param_sell);
    
                            if (!$is_disabled_lelang) {
                                $param_auction = [
                                    'domain' => $domain,
                                    'price' => $auction->price,
                                    'status' => 'OPEN_LELANG',
                                    'owner' => $uid,
                                ];
                                DB::table('auction_domain')->insert($param_auction);
                            }
                        } else {
                            $message_error = 'Nameserver tidak terverifikasi';
                            Session::flash('alert-type', 'danger');
                            Session::flash('alert-message', $message_error);
                            return redirect()->route('pages.domain.selldomains.index', [
                                'page' => 'insert',
                                'section' => 'modal',
                                'domain' => $domain,
                            ]);
                        }
                    } else {
                        $message_error = 'Nameserver tidak terverifikasi';
                        Session::flash('alert-type', 'danger');
                        Session::flash('alert-message', $message_error);
                        return redirect()->route('pages.domain.selldomains.index', [
                            'page' => 'insert',
                            'section' => 'modal',
                            'domain' => $domain,
                        ]);
                    }
                }

                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'list',
                ]);
            break;

            case 'download_html':
                $domain = $request->input('domain');
            
                $isSellDomain = DB::table('sell_domain')->where('domain', $domain)->first();

                if ($isSellDomain) {
                    $htmlfile = $sellDomain->getTokenHTML($isSellDomain->domain);
                } else {
                    $isRentDomain = DB::table('rent_domain')->where('domain', $domain)->first();
                    
                    if ($isRentDomain) {
                        $htmlfile = $rentDomain->getTokenHTML($isRentDomain->domain);
                    } else {
                        return redirect()->back()->with([
                            'alert-type' => 'danger',
                            'alert-message' => 'Domain tidak ditemukan'
                        ]);
                    }
                }
                
                // Gunakan Storage untuk menyimpan file
                $directory = 'sell_domain/';
                $fileName = $htmlfile;
                $content = "site-verification: {$htmlfile}";

                // Pastikan direktori ada
                if (!Storage::exists($directory)) {
                    Storage::makeDirectory($directory);
                }

                // Simpan file
                Storage::put($directory . $fileName, $content);

                // Mendapatkan path untuk download
                $path = storage_path('app/' . $directory . $fileName);
                
                // Sekarang download file
                if (file_exists($path)) {
                    return response()->download($path);
                } else {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'File HTML tidak ditemukan'
                    ]);
                }
            break;

            case 'set_bank':
                // Validation
                $formtoken = $request->input('sell_token');
                if ($formtoken !== Session::get('sell_token')) {
                    $message_error = 'Token tidak cocok';
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => $message_error
                    ]);
                }
    
                $fields = $request->input('field', []);
                $bankname = $fields['bank'] ?? null;
                $rekening = $fields['rekening'] ?? null;
                $atasnama = $fields['an'] ?? null;
    
                if ($bankname && $rekening) {
                    $sellDomain->DBsetBank($bankname, $rekening, $atasnama);
                    $message = 'Sukses setting bank';
    
                    return redirect()->back()->with([
                        'alert-type' => 'success',
                        'alert-message' => $message
                    ]);
                } else {
                    $message_error = 'Ada Field Kosong';
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => $message_error
                    ]);
                }
            break;

            case 'edit':
                // Validation
                $formtoken = $request->input('sell_token');
                $domain = $request->input('domain');
                $uid = Auth::user()->id;
                $is_disabled_lelang = $request->input('is_disabled_lelang', false);
                
                $valid = $sellDomain->validateDomain($domain);
                if (!$valid) {
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', 'Domain not allowed');
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'edit',
                        'domain' => $domain,
                    ]);
                }
    
                if ($formtoken !== Session::get('sell_token')) {
                    // Handle token mismatch
                    $message_error = 'Token tidak cocok';
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', $message_error);
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'edit',
                        'domain' => $domain,
                    ]);
                }
    
                if ($sellDomain->cekSudahAdaYgNgebid($domain)) {
                    $message_error = 'Domain sudah ada yang nge BID';
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', $message_error);
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'edit',
                        'domain' => $domain,
                    ]);
                }
    
                $price_kontan = $request->input('price_kontan');
                $price_awal = $request->input('price_awal');
                $price_sewa = $request->input('price_sewa');
    
                // Update rent
                DB::table('rent_domain')
                    ->where('domain', $domain)
                    ->where('clientid', $uid)
                    ->update(['price' => $price_sewa]);
    
                // Update auction and sell domain
                $cek_lelang = DB::table('sell_domain')->where('domain', $domain)->where('type', 'AUCTION_PRICE')->first();
                $cek_fix = DB::table('sell_domain')->where('domain', $domain)->where('type', 'FIX_PRICE')->first();
    
                if (!$cek_lelang && $cek_fix && $price_awal) {
                    if (!$is_disabled_lelang) {
                        DB::table('sell_domain')->insert([
                            'domain' => $domain,
                            'clientid' => $uid,
                            'type' => 'AUCTION_PRICE',
                            'price' => $price_awal,
                            'uniqid' => $cek_fix->uniqid,
                            'status' => $cek_fix->status,
                            'enabled' => $cek_fix->enabled
                        ]);
                    } else {
                        DB::table('sell_domain')
                            ->where('domain', $domain)
                            ->where('clientid', $uid)
                            ->where('type', 'AUCTION_PRICE')
                            ->limit(1)
                            ->delete();
                    }
                } else {
                    if ($price_awal) {
                        if (!$is_disabled_lelang) {
                            DB::table('sell_domain')
                                ->where('domain', $domain)
                                ->where('clientid', $uid)
                                ->where('type', 'AUCTION_PRICE')
                                ->update(['price' => $price_awal]);
                        } else {
                            DB::table('sell_domain')
                                ->where('domain', $domain)
                                ->where('clientid', $uid)
                                ->where('type', 'AUCTION_PRICE')
                                ->limit(1)
                                ->delete();
                        }
                    } else {
                        if ($is_disabled_lelang) {
                            DB::table('sell_domain')
                                ->where('domain', $domain)
                                ->where('clientid', $uid)
                                ->where('type', 'AUCTION_PRICE')
                                ->limit(1)
                                ->delete();
                        }
                    }
                }
                
                try {
                    DB::table('sell_domain')
                        ->where('domain', $domain)
                        ->where('clientid', $uid)
                        ->where('type', 'FIX_PRICE')
                        ->update(['price' => $price_kontan]);
                } catch (\Exception $e) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal edit harga: ' . $e->getMessage()
                    ]);
                }
    
                $message = "Sukses edit harga";
                Session::flash('alert-type', 'success');
                Session::flash('alert-message', $message);  
                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'edit',
                    'domain' => $domain,
                ]);
            break;

            case 'set_epp':
                $epp = $request->input('epp');
                $formtoken = $request->input('sell_token');
                $domain = $request->input('domain');
                
                $valid = $sellDomain->validateDomain($domain);
                // $valid = true; //HARDCODE
                if (!$valid) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Domain not allowed'
                    ]);
                }
    
                if ($formtoken !== Session::get('sell_token')) {
                    $message_error = 'Token tidak cocok';
                    Session::flash('alert-type', 'danger');
                    Session::flash('alert-message', $message_error);
                    return redirect()->route('pages.domain.selldomains.index', [
                        'page' => 'epp',
                        'domain' => $domain,
                    ]);
                }
    
                $epp = $request->input('epp');
                $sellDomain->DBsetEPP($domain, $epp);
                $message = "Sukses kirim Epp";

                Session::flash('alert-type', 'success');
                Session::flash('alert-message', $message);
                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'epp',
                    'domain' => $domain,
                ]);
            break;

            case 'toggle':
                $domain = $request->input('domain');
                $val_toggle = $request->input('val_toggle');
    
                $valid = $sellDomain->validateDomain($domain);
                if (!$valid) {
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Domain not allowed'
                    ]);
                }
    
                $val = $val_toggle === 'true' ? 1 : 0;
                $res = $sellDomain->enableDomain($domain, $val);

                Session::flash('alert-type', 'success');
                Session::flash('alert-message', $res['message']);
                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'list',
                ]);
            break;

            case 'delete':
                $domain = $request->input('domain');
                $valid = $sellDomain->validateDomain($domain);
                if (!$valid) {
                    // abort(403, 'Domain not allowed');
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Domain not allowed'
                    ]);
                }
    
                $res = $sellDomain->deleteDomain($domain);
                Session::flash('alert-type', 'success');
                Session::flash('alert-message', $res['message']);
                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'list',
                ]);
            break;

            case 'calculate_price':
                $price = $request->input('price');
                $clientid = Auth::user()->id;

                $dana = $sellDomain->getDanaYangBisaDicairkan($price, $clientid);

                return response()->json($dana);
            break;
    
            case 'rent':
                $auth = Auth::user();
                $clientid = $auth->id;

                $domain = $request->input('domain');

                $result = $rentDomain->rent_domain($domain, $clientid);

                if($result['result'] == 'error'){
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal membuat invoice: ' . $result['message']
                    ]);
                }

                return redirect()->route('pages.services.mydomains.viewinvoiceweb', $result['invoiceid']);
                // $invoiceid = $res['invoiceid'];
    
                // if ($invoiceid) {
                //     return redirect()->to("/viewinvoice.php?id=$invoiceid");
                // } else {
                //     $msg = $res['message'];
                //     Session::flash('alert-type', 'danger');
                //     Session::flash('alert-message', $msg);
                //     return redirect()->route('pages.domain.selldomains.index', [
                //         'page' => 'rent_all',
                //     ]);
                // }
            break;
    
            case 'setup_epp_rent':
                $epp = $request->input('epp');
                $domain = $request->input('domain');
                
                $res = $rentDomain->insertEPP($domain, $epp);

                if($res['status'] == 'error'){
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => $res['message']
                    ]);
                }

                Session::flash('alert-type', 'success');
                Session::flash('alert-message', $res['message']);
                return redirect()->route('pages.domain.selldomains.index', [
                    'page' => 'list'
                ]);
            break;
    
            case 'delete_rent':
                $auth = Auth::user();
                $clientid = $auth->id;
                $domain = $request->input('domain');
                
                // select rent domain
                $rentDomain = DB::table('rent_domain')->where('domain', $domain)->first();
                if($rentDomain->clientid != $clientid){
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Hapus domain tidak diizinkan!'
                    ]);
                }

                try {
                    DB::beginTransaction();
                    DB::table('rent_domain')
                        ->where('clientid', $clientid)
                        ->where('domain', $domain)
                        ->delete();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with([
                        'alert-type' => 'danger',
                        'alert-message' => 'Gagal delete domain'
                    ]);
                }

                return redirect()->back()->with([
                    'alert-type' => 'success',
                    'alert-message' => 'Hapus domain . berhasil!'
                ]);
            break;
    
            case 'check_domain_age':
                $domain = $request->input('domain');
                try {
                    $domainHelper = new Domains();
                    $domainAge = $domainHelper->DomainWhois($domain);

                    return response()->json([
                        'raw' => $domainAge,
                        'success' => true,
                        'results' => [
                            'result' => true,
                            'whois_status' => $domainAge > 60 ? 'available' : 'unavailable'
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error checking domain age: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to check domain age',
                        'error' => $e->getMessage()
                    ], 500);
                }
            break;
        }
    }











    // route get to transferdomains
    public function Domain_TransferDomain()
    {
        return view('pages.domain.transferdomain.index');
    }
    // route get to transferdomains

    // route post to transferdomain.register
    public function Domain_SetupTransfer(Request $request)
    {
        $domainName = $request->domain;
        return view('pages.domain.transferdomain.register', compact('domainName'));
    }
    // route post to transferdomain.register

    // route get to generatecertificate
    public function Generate_Domain_Certificate()
    {
        $path = asset('/assets/certificate/template-certificate.jpg');
        $image = imagecreatefromjpeg($path);
        $color = imagecolorallocate($image, 255, 255, 255);
        $string = 'The string you want to write horizontally on the image';
        $fontSize = 3;
        $x = 300;
        $y = 400;

        imagestring($image, $fontSize, $x, $y, $string, $color);

        $testImage = imagejpeg($image,  $fileName = asset('assets/generatedc'), $quality = 100);

        return $testImage;
    }
    // route get to generatecertificate

    // route get to dt_myDomains
    public function dt_myDomains()
    {
        $auth = Auth::user();
        $userid = $auth->id;

        $getDomain = Domain::where("userid", $userid)->orderBy("id", "DESC")->get();
        return datatables()->of($getDomain)->editColumn('name', function ($row) {
            return $row->domain;
        })
            ->editColumn('registrationdate', function ($row) {
                $regDate = $row->registrationdate;
                return date('d-m-Y', strtotime($regDate));
            })
            ->editColumn('nextduedate', function ($row) {
                $dueDate = $row->nextduedate;
                return date('d-m-Y', strtotime($dueDate));
            })
            ->editColumn('status', function ($row) {
                $status = $row->status;
                switch ($status) {
                    case 'Active':
                        return "<span class=\"badge badge-success\">{$status}</span>";
                        break;
                    case 'Grace':
                        return "<span class=\"badge badge-secondary\">{$status}</span>";
                        break;
                    case 'Expired':
                        return "<span class=\"badge badge-danger\">{$status}</span>";
                        break;
                    case 'Pending':
                        return "<span class=\"badge badge-warning\">{$status}</span>";
                        break;
                    default:
                        return "<span class=\"badge badge-dark\">Unknown</span>";
                        break;
                }
            })
            ->editColumn('donotrenew', function ($row) {
                $renew = $row->donotrenew;
                switch ($renew) {
                    case '1':
                        return "<span class=\"badge badge-success\">Active</span>";
                        break;
                    case '0':
                        return "<span class=\"badge badge-soft-danger\">Inactive</span>";
                        break;
                    default:
                        return "<span class=\"badge badge-dark\">Not Set</span>";
                        break;
                }
            })
            ->editColumn('actions', function ($row) {
                $detailRoute = route('pages.domain.mydomains.domaindetails2') . "?action=domaindetails&id=" . $row->id;
                $childNameserverRoute = route('pages.domain.mydomains.childnameservers') . '?id=' . $row->id;
                $detailRoute = route('pages.domain.mydomains.details') . '?id=' . $row->id;

                $action = '';
                $action .= "<a href=\"{$detailRoute}\" class=\"btn btn-outline-primary my-1 mr-2 px-3\" title=\"Details\"><i class=\"fas fa-search\"></i></a>";
                $action .= "<div class=\"btn-group my-1\" role=\"group\">
                                       <button id=\"btnGroupDrop1\" type=\"button\"
                                          class=\"btn btn-outline-secondary dropdown-toggle\" data-toggle=\"dropdown\"
                                          aria-haspopup=\"true\" aria-expanded=\"false\">
                                          <i class=\"feather-settings\"></i> <i class=\"mdi mdi-chevron-down\"></i>
                                       </button>
                            <div class=\"dropdown-menu\" aria-labelledby=\"btnGroupDrop1\"> 
                                <a class=\"dropdown-item\" onclick=\"getDomainDetails(this)\" href=\"#atur-name-server\" data-domain-id=\"{$row->id}\" data-toggle=\"modal\">Atur nameserver</a>
                                <a class=\"dropdown-item\" href=\"{$childNameserverRoute}\">Tambah Child Nameserver</a>
                                <a class=\"dropdown-item\" href=\"#domain-dns-manager\" data-toggle=\"modal\">Domain
                                DNS manager</a>
                                <a class=\"dropdown-item\" href=\"#ubah-informasi-kontak\" data-toggle=\"modal\">Ubah
                                informasi kontak</a>
                                <a class=\"dropdown-item\" onclick=\"getDomainDetails(this)\" data-domain-id=\"{$row->id}\" data-domain-status=\"autorenew\" href=\"#perpanjangan-otomatis\"
                                data-toggle=\"modal\">Perpanjangan otomatis</a>
                                <a class=\"dropdown-item\" href=\"{$detailRoute}\">Atur Domain</a>
                            </div>
                           </div>";
                return $action;
            })
            ->rawColumns(['status', 'donotrenew', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }
    // route get to dt_myDomains

    public function Domains_DetailDomain($id)
    {
        $auth = Auth::user();
        $userid = $auth->id;

        $domains = new Domains();
        $domain_data = $domains->getDomainsDatabyID($id);

        //First Payment Amount
        $firstPaymentAmount = Format::formatCurrency($domain_data['firstpaymentamount']);

        //Recurring Amount
        $recurringAmount = Format::formatCurrency($domain_data['recurringamount']);

        //Payment Method
        $gatewayArray = Gateway::GetGatewaysArray();
        $paymentMethod = $gatewayArray[$domain_data['paymentmethod']] ?? 'Unknown';

        //SSL checker
        $sslStatus = Sslstatus::factory($userid, $domain_data['domain']);
        $resSslStats = $sslStatus->getStatus();
        $sslLabel = $sslStatus->getStatusDisplayLabel();
        $lockIcon = '';
        $path = asset("/assets/ssl/ssl-" . $resSslStats . ".png");

        if ($resSslStats === __("admin.sslStatevalidSsl")) {
            $lockIcon .= "<img src=\"{$path}\" data-toggle=\"tooltip\" title=\"Active\" style=\"width:25px;\">";
        } else {
            $lockIcon .= "<img src=\"{$path}\" data-toggle=\"tooltip\" title=\"Inactive\" style=\"width:25px;\">";
        }

        return view('pages.domain.mydomains.domaindetails', [
            'domain_data' => $domain_data,
            'sslStatus' => $sslStatus,
            'sslLabel' => $sslLabel,
            'lockIcon' => $lockIcon,
            'resSslStats' => $resSslStats,
            'recurringAmount' => $recurringAmount,
            'firstPaymentAmount' => $firstPaymentAmount,
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function Domains_DetailDomain2(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $route = "pages.domain.mydomains.domaindetails2";

        $action = $request->input("action");
        $sub = $request->input("sub");
        $id = $request->input("id");
        $modop = $request->input("modop");
        $submit = $request->input("submit");
        $save = $request->input("save");
        $q = $request->input("q");
        $paymentmethod = \App\Helpers\Gateways::makeSafeName($request->input("paymentmethod"));
        $params = array();
        $addRenewalToCart = $request->input("addRenewalToCart");

        $domainID = $request->input("id") ?? $id;
        if (!$domainID) {
            $domainID = $request->input("domainid") ?? $id;
        }
        $domains = new \App\Helpers\DomainsClass();
        $domainData = $domains->getDomainsDatabyID($domainID);
        if (!$domainData) {
            // redir("action=domains", "clientarea.php");
            return redirect()->route($route, ['action' => 'domains']);
        }
        $domainModel = \App\Models\Domain::find($domainData["id"]);
        // $ca->setDisplayTitle(\Lang::get("managing") . " " . $domainData["domain"]);
        $domainName = new \App\Helpers\Domain\Domain($domainData["domain"]);
        $managementOptions = $domains->getManagementOptions();
        if ($domainModel->registrarModuleName) {
            $registrar = new \App\Module\Registrar();
            $registrar->setDomainID($domainModel->id);
            if ($registrar->load($domainModel->registrarModuleName)) {
                $params = $registrar->getSettings();
            }
        }
        $smartyvalues["managementoptions"] = $managementOptions;

        $legacyClient = new \App\Helpers\ClientClass($auth->id);
        $clientInformation = $legacyClient->getClientModel();
        $currency = $legacyClient->getCurrency();
        $today = \App\Helpers\Carbon::today();
        $templatefile = "";

        // domaindetails
        if ($action == 'domaindetails') {
            $templatefile = "pages.domain.mydomains.domaindetails";
            $domain_data = $domains->getDomainsDatabyID($domainID);
            $smartyvalues["changeAutoRenewStatusSuccessful"] = false;
            if ($domains->getData("status") == "Active") {
                $autorenew = $request->input("autorenew");
                if ($autorenew == "enable") {
                    // check_token();
                    // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                    \App\Models\Domain::where(array("id" => $id, "userid" => $legacyClient->getID()))->update(array("donotrenew" => ""));
                    \App\Helpers\LogActivity::Save("Client Enabled Domain Auto Renew - Domain ID: " . $id . " - Domain: " . $domainName->getDomain());
                    $smartyvalues["updatesuccess"] = true;
                    $smartyvalues["changeAutoRenewStatusSuccessful"] = true;
                } else {
                    if ($autorenew == "disable") {
                        // check_token();
                        // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                        \App\Helpers\Domain::disableAutoRenew($id);
                        $smartyvalues["updatesuccess"] = true;
                        $smartyvalues["changeAutoRenewStatusSuccessful"] = true;
                    }
                }
                $domain_data = $domains->getDomainsDatabyID($domainID);
            }
            $domain = $domains->getData("domain");
            $firstpaymentamount = $domains->getData("firstpaymentamount");
            $recurringamount = $domains->getData("recurringamount");
            $nextduedate = $domains->getData("nextduedate");
            $expirydate = $domains->getData("expirydate");
            $paymentmethod = $domains->getData("paymentmethod");
            $domainstatus = $domains->getData("status");
            $registrationperiod = $domains->getData("registrationperiod");
            $registrationdate = $domains->getData("registrationdate");
            $donotrenew = $domains->getData("donotrenew");
            $dnsmanagement = $domains->getData("dnsmanagement");
            $emailforwarding = $domains->getData("emailforwarding");
            $idprotection = $domains->getData("idprotection");
            $registrar = $domains->getModule();
            $gatewaysarray = \App\Helpers\Gateway::getGatewaysArray();
            $paymentmethod = $gatewaysarray[$paymentmethod] ?? $paymentmethod;
            // $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=" . $domain_data["id"], $domain);
            $registrationdate = (new \App\Helpers\Functions)->fromMySQLDate($registrationdate, 0, 1, "-");
            $nextduedate = (new \App\Helpers\Functions)->fromMySQLDate($nextduedate, 0, 1, "-");
            $expirydate = (new \App\Helpers\Functions)->fromMySQLDate($expirydate, 0, 1, "-");
            // $rawstatus = $ca->getRawStatus($domainstatus);
            $val = strtolower($domainstatus);
            $val = str_replace(" ", "", $val);
            $val = str_replace("-", "", $val);
            $rawstatus = $val;
            $allowrenew = false;
            if (\App\Helpers\Cfg::get("EnableDomainRenewalOrders") && in_array($domainstatus, array("Active", "Grace", "Redemption", "Expired"))) {
                $allowrenew = true;
            }
            $autorenew = $donotrenew ? false : true;
            $smartyvalues["domainid"] = $domains->getData("id");
            $smartyvalues["domain"] = $domain;
            $smartyvalues["firstpaymentamount"] = \App\Helpers\Format::formatCurrency($firstpaymentamount);
            $smartyvalues["recurringamount"] = \App\Helpers\Format::formatCurrency($recurringamount);
            $smartyvalues["registrationdate"] = $registrationdate;
            $smartyvalues["nextduedate"] = $nextduedate;
            $smartyvalues["expirydate"] = $expirydate;
            $smartyvalues["registrationperiod"] = $registrationperiod;
            $smartyvalues["paymentmethod"] = $paymentmethod;
            $smartyvalues["systemStatus"] = $domainstatus;
            $smartyvalues["canDomainBeManaged"] = in_array($domainstatus, array(\App\Models\Domain::ACTIVE, \App\Models\Domain::GRACE, \App\Models\Domain::REDEMPTION));
            $smartyvalues["status"] = \Lang::get("client.clientarea" . $rawstatus);
            $smartyvalues["rawstatus"] = $rawstatus;
            $smartyvalues["donotrenew"] = $donotrenew;
            $smartyvalues["autorenew"] = $autorenew;
            $smartyvalues["subaction"] = $sub;
            $smartyvalues["addonstatus"] = array("dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection);
            if ($allowrenew) {
                $smartyvalues["renew"] = $allowrenew;
            }
            $tlddata = \App\Models\Domainpricing::where(array("extension" => "." . $domainName->getTLD()));
            $smartyvalues["addons"] = array("dnsmanagement" => $tlddata->value("dnsmanagement"), "emailforwarding" => $tlddata->value("emailforwarding"), "idprotection" => $tlddata->value("idprotection"));
            $addonscount = 0;
            if ($tlddata->value("dnsmanagement")) {
                $addonscount++;
            }
            if ($tlddata->value("emailforwarding")) {
                $addonscount++;
            }
            if ($tlddata->value("idprotection")) {
                $addonscount++;
            }
            $smartyvalues["addonscount"] = $addonscount;
            $result = \App\Models\Pricing::where(array("type" => "domainaddons", "currency" => $currency["id"], "relid" => 0));
            $data = $result;
            $domaindnsmanagementprice = $data->value("msetupfee") ?? 0;
            $domainemailforwardingprice = $data->value("qsetupfee") ?? 0;
            $domainidprotectionprice = $data->value("ssetupfee") ?? 0;
            $smartyvalues["addonspricing"] = array("dnsmanagement" => \App\Helpers\Format::formatCurrency($domaindnsmanagementprice), "emailforwarding" => \App\Helpers\Format::formatCurrency($domainemailforwardingprice), "idprotection" => \App\Helpers\Format::formatCurrency($domainidprotectionprice));
            $smartyvalues["updatesuccess"] = false;
            $smartyvalues["registrarcustombuttonresult"] = "";
            $smartyvalues["lockstatus"] = "";
            if ($domainstatus == "Active" && $domains->getModule()) {
                $registrarclientarea = "";
                $smartyvalues["registrar"] = $registrar;
                if ($sub == "savens") {
                    // check_token();
                    // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                    $nschoice = $request->input('nschoice');
                    $ns1 = $request->input('ns1');
                    $ns2 = $request->input('ns2');
                    $ns3 = $request->input('ns3');
                    $ns4 = $request->input('ns4');
                    $ns5 = $request->input('ns5');
                    $nameservers = $nschoice == "default" ? $domains->getDefaultNameservers() : array("ns1" => $ns1, "ns2" => $ns2, "ns3" => $ns3, "ns4" => $ns4, "ns5" => $ns5);
                    $success = $domains->moduleCall("SaveNameservers", $nameservers);
                    if ($success) {
                        $smartyvalues["updatesuccess"] = true;
                    } else {
                        $smartyvalues["error"] = $domains->getLastError();
                    }
                }
                if ($sub == "savereglock") {
                    // check_token();
                    // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                    $newlockstatus = $request->input("reglock") ? "locked" : "unlocked";
                    $success = $domains->moduleCall("SaveRegistrarLock", array("lockenabled" => $newlockstatus));
                    if ($success) {
                        $smartyvalues["updatesuccess"] = true;
                    } else {
                        $smartyvalues["error"] = $domains->getLastError();
                    }
                }
                $alerts = array();
                if ($sub == "resendirtpemail" && $domains->hasFunction("ResendIRTPVerificationEmail")) {
                    // check_token();
                    // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                    $success = $domains->moduleCall("ResendIRTPVerificationEmail");
                    if ($success) {
                        $alerts[] = array("title" => \Lang::get("domains.resendNotification"), "description" => \Lang::get("domains.resendNotificationSuccess"), "type" => "success");
                    } else {
                        $error = $domains->getLastError();
                        $alerts[] = array("title" => \Lang::get("domains.resendNotification"), "description" => $error, "type" => "danger");
                    }
                }
                $nameserversArray = array();
                for ($i = 1; $i <= 5; $i++) {
                    $nameserversArray[$i] = array("num" => $i, "label" => \Lang::get("client.domainnameserver" . $i), "value" => "");
                }
                $smartyvalues["defaultns"] = false;
                $smartyvalues["nameservers"] = array();
                $showResendIRTPVerificationEmail = false;
                try {
                    $domainInformation = $domains->getDomainInformation();
                    $nsValues = $domainInformation->getNameservers();
                    // dd($nsValues);
                    $i = 1;
                    foreach ($nsValues as $nameserver) {
                        $smartyvalues["ns$i"] = $nameserver;
                        $nameserversArray[$i]["value"] = $nameserver;
                        $i++;
                    }
                    $smartyvalues["managens"] = true;
                    $smartyvalues["nameservers"] = $nameserversArray;
                    $defaultNameservers = array();
                    for ($i = 1; $i <= 5; $i++) {
                        if (trim(\App\Helpers\Cfg::getValue("DefaultNameserver" . $i))) {
                            $defaultNameservers[] = strtolower(trim(\App\Helpers\Cfg::getValue("DefaultNameserver" . $i)));
                        }
                    }
                    $isDefaultNs = true;
                    foreach ($nameserversArray as $nsInfo) {
                        $ns = $nsInfo["value"];
                        if ($ns && !in_array($ns, $defaultNameservers)) {
                            $isDefaultNs = false;
                            break;
                        }
                    }
                    $smartyvalues["defaultns"] = $isDefaultNs;
                    if ($managementOptions["locking"]) {
                        $lockStatus = "unlocked";
                        if ($domainInformation->getTransferLock()) {
                            $lockStatus = "locked";
                        }
                        $smartyvalues["lockstatus"] = $lockStatus;
                    }
                    if ($domainInformation->isIrtpEnabled() && $domainInformation->isContactChangePending()) {
                        $title = \Lang::get("client.domainscontactChangePending");
                        $descriptionLanguageString = "client.domainscontactsChanged";
                        if ($domainInformation->getPendingSuspension()) {
                            $title = \Lang::get("client.domainsverificationRequired");
                            $descriptionLanguageString = "client.domainsnewRegistration";
                        }
                        $parameters = array();
                        if ($domainInformation->getDomainContactChangeExpiryDate()) {
                            $descriptionLanguageString .= "Date";
                            $parameters = array("date" => $domainInformation->getDomainContactChangeExpiryDate()->toClientDateFormat());
                        }
                        $resendButton = \Lang::get("client.domainsresendNotification");
                        $description = \Lang::get($descriptionLanguageString, $parameters);
                        $description .= "<br>\n<form method=\"post\" action=\"?action=domaindetails#tabOverview\">\n    <input type=\"hidden\" name=\"id\" value=\"" . $domain_data["id"] . "\">\n    <input type=\"hidden\" name=\"sub\" value=\"resendirtpemail\" />\n    <button type=\"submit\" class=\"btn btn-sm btn-primary\">" . $resendButton . "</button>\n</form>";
                        $alerts[] = array("title" => $title, "description" => $description, "type" => "info");
                        $showResendIRTPVerificationEmail = true;
                    }
                    if ($domainInformation->isIrtpEnabled() && $domainInformation->getIrtpTransferLock()) {
                        $title = \Lang::get("client.domainsirtpLockEnabled");
                        $descriptionLanguageString = \Lang::get("client.domainsirtpLockDescription");
                        if ($domainInformation->getIrtpTransferLockExpiryDate()) {
                            $descriptionLanguageString = \Lang::get("client.domainsirtpLockDescriptionDate", array(":date" => $domainInformation->getIrtpTransferLockExpiryDate()->toClientDateFormat()));
                        }
                        $alerts[] = array("title" => $title, "description" => $descriptionLanguageString, "type" => "info");
                    }
                } catch (\Exception $e) {
                    $smartyvalues["nameservererror"] = $e->getMessage();
                    $smartyvalues["error"] = $smartyvalues["nameservererror"];
                }
                if ($alerts) {
                    $smartyvalues["alerts"] = $alerts;
                }
                $smartyvalues["showResendVerificationEmail"] = $showResendIRTPVerificationEmail;
                $smartyvalues["managecontacts"] = $managementOptions["contacts"];
                $smartyvalues["registerns"] = $managementOptions["privatens"];
                $smartyvalues["dnsmanagement"] = $managementOptions["dnsmanagement"];
                $smartyvalues["emailforwarding"] = $managementOptions["emailforwarding"];
                $smartyvalues["getepp"] = $managementOptions["eppcode"];
                if ($managementOptions["release"]) {
                    $allowrelease = false;
                    if (isset($params["AllowClientTAGChange"]) && !$params["AllowClientTAGChange"]) {
                        $managementOptions["release"] = false;
                        $smartyvalues["managementOptions"] = $managementOptions;
                    }
                    if ($managementOptions["release"]) {
                        $smartyvalues["releasedomain"] = true;
                        if ($sub == "releasedomain") {
                            // check_token();
                            // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                            $success = $domains->moduleCall("ReleaseDomain", array("transfertag" => $transtag));
                            if ($success) {
                                DB::table("tbldomains")->where("id", $domains->getData("id"))->update(array("status" => "Transferred Away"));
                                $smartyvalues["status"] = \Lang::get("client.clientareatransferredaway");
                                \App\Helpers\LogActivity::Save("Client Requested Domain Release to Tag " . $transtag);
                            } else {
                                $smartyvalues["error"] = $domains->getLastError();
                            }
                        }
                    } else {
                        $smartyvalues["releasedomain"] = false;
                    }
                }
                $allowedclientregistrarfunctions = array();
                if ($domains->hasFunction("ClientAreaAllowedFunctions")) {
                    $success = $domains->moduleCall("ClientAreaAllowedFunctions");
                    $registrarallowedfunctions = $domains->getModuleReturn();
                    if (is_array($registrarallowedfunctions)) {
                        foreach ($registrarallowedfunctions as $v) {
                            $allowedclientregistrarfunctions[] = $v;
                        }
                    }
                }
                if ($domains->hasFunction("ClientAreaCustomButtonArray")) {
                    $success = $domains->moduleCall("ClientAreaCustomButtonArray");
                    $registrarcustombuttons = $domains->getModuleReturn();
                    if (is_array($registrarcustombuttons)) {
                        foreach ($registrarcustombuttons as $k => $v) {
                            $allowedclientregistrarfunctions[] = $v;
                        }
                    }
                    $smartyvalues["registrarcustombuttons"] = $registrarcustombuttons;
                }
                $a = $request->input('a');
                // HOTFIX: this
                if ($modop == "custom" && in_array($a, $allowedclientregistrarfunctions)) {
                    // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
                    $success = $domains->moduleCall($a);
                    $data = $domains->getModuleReturn();
                    if (is_array($data)) {
                        if (isset($data["templatefile"])) {
                            if (!\Module::find($registrar)) {
                                throw new \App\Exceptions\Fatal("Invalid Registrar Module Name");
                            }
                            if (!\App\Helpers\Functions::isValidforPath($data["templatefile"])) {
                                throw new \App\Exceptions\Fatal("Invalid Template Filename");
                            }
                            // TODO: $ca->setTemplate("/modules/registrars/" . $registrar . "/" . $data["templatefile"] . ".tpl");
                        }
                        if (isset($data["breadcrumb"]) && is_array($data["breadcrumb"])) {
                            foreach ($data["breadcrumb"] as $k => $v) {
                                // $ca->addToBreadCrumb($k, $v);
                            }
                        }
                        if (isset($data["vars"]) && is_array($data["vars"])) {
                            foreach ($data["vars"] as $k => $v) {
                                $smartyvalues[$k] = $v;
                            }
                        }
                    } else {
                        if (!$data || $data == "success") {
                            $smartyvalues["registrarcustombuttonresult"] = "success";
                        } else {
                            $smartyvalues["registrarcustombuttonresult"] = $data;
                        }
                    }
                }
                if (\App\Helpers\ClientHelper::checkContactPermission("managedomains", true)) {
                    $moduletemplatefile = "";
                    $result = \App\Models\Domain::where(array("id" => $domains->getData("id")));
                    $data = $result;
                    $idprotection = $data->value("idprotection") ? true : false;
                    $success = $domains->moduleCall("ClientArea", array("protectenable" => $idprotection));
                    $result = $domains->getModuleReturn();
                    // module interface
                    $moduleInterface = new \App\Module\Registrar();
                    $moduleInterface->load($domains->getModule());
                    if (is_array($result)) {
                        if (isset($result["templatefile"])) {
                            if (!\Module::find($registrar)) {
                                throw new \App\Exceptions\Fatal("Invalid Registrar Module Name");
                            }
                            if (!\App\Helpers\Functions::isValidforPath($result["templatefile"])) {
                                throw new \App\Exceptions\Fatal("Invalid Template Filename");
                            }
                            // $moduletemplatefile = "/modules/registrars/" . $registrar . "/" . $result["templatefile"] . ".tpl";
                            $moduletemplatefile = $moduleInterface->findTemplate($result["templatefile"]);
                        }
                    } else {
                        $registrarclientarea = $result;
                    }
                    // find template clientarea on module its self
                    $clientareaBlade = $moduleInterface->findTemplate("clientarea");
                    if (!$moduletemplatefile && \Module::find($registrar) && \View::exists($clientareaBlade)) {
                        // $moduletemplatefile = "/modules/registrars/" . $registrar . "/clientarea.tpl";
                        $moduletemplatefile = $clientareaBlade;
                    }
                    if ($moduletemplatefile) {
                        if (isset($result["vars"]) && is_array($result["vars"])) {
                            foreach ($result["vars"] as $k => $v) {
                                $params[$k] = $v;
                            }
                        }
                        // TODO: $registrarclientarea = $ca->getSingleTPLOutput($moduletemplatefile, $moduleparams);
                        $registrarclientarea = view($moduletemplatefile, $moduleInterface->getParams())->render();
                    }
                }
                $smartyvalues["registrarclientarea"] = $registrarclientarea;
            }
            $sslStatus = \App\Models\Sslstatus::factory($legacyClient->getID(), $domain)->syncAndSave();
            $smartyvalues["sslStatus"] = $sslStatus;
            $invoice = DB::table("tblinvoices")->join("tblinvoiceitems", function ($join) use ($domainData) {
                $join->on("tblinvoices.id", "=", "tblinvoiceitems.invoiceid")->whereIn("tblinvoiceitems.type", array("DomainRegister", "DomainTransfer", "Domain"))->where("tblinvoiceitems.relid", "=", $domainData["id"]);
            })->where("tblinvoices.status", "Unpaid")->orderBy("tblinvoices.duedate", "asc")->first(array("tblinvoices.id", "tblinvoices.duedate"));
            $invoiceId = NULL;
            $overdue = false;
            $smartyvalues["unpaidInvoiceMessage"] = "";
            if ($invoice) {
                $invoiceId = $invoice->id;
                $dueDate = \App\Helpers\Carbon::createFromFormat("Y-m-d", $invoice->duedate);
                $overdue = $today->gt($dueDate);
                $languageString = "unpaidInvoiceAlert";
                if ($overdue) {
                    $languageString = "overdueInvoiceAlert";
                }
                $smartyvalues["unpaidInvoiceMessage"] = \Lang::get("client." . $languageString);
            }
            $smartyvalues["unpaidInvoice"] = $invoiceId;
            $smartyvalues["unpaidInvoiceOverdue"] = $overdue;
            \App\Helpers\Hooks::run_hook("ClientAreaDomainDetails", array("domain" => $domainModel));
            $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaDomainDetailsOutput", array("domain" => $domainModel));
            $smartyvalues["hookOutput"] = $hookResponses;
            // $ca->addOutputHookFunction("ClientAreaPageDomainDetails");
        }
        // domaincontacts
        if ($action == 'domaincontacts') {
            $templatefile = "pages.domain.mydomains.domaincontacts";
            // \App\Helpers\ClientHelper::checkContactPermission("managedomains");
            // $ca->setTemplate("clientareadomaincontactinfo");
            $contactsarray = $legacyClient->getContactsWithAddresses();
            $smartyvalues["contacts"] = $contactsarray;
            if (!$domainData || !$domains->isActive() || !$domains->hasFunction("GetContactDetails")) {
                // redir("action=domains", "clientarea.php");
                return redirect()->route($route, ['action' => 'domains']);
            }
            // $ca->addToBreadCrumb("clientarea.php?action=domaindetails&id=" . $domainData["id"], $domainData["domain"]);
            // $ca->addToBreadCrumb("#", $whmcs->get_lang("domaincontactinfo"));
            $smartyvalues["successful"] = false;
            $smartyvalues["pending"] = false;
            $smartyvalues["error"] = "";
            $smartyvalues["domainInformation"] = "";
            $smartyvalues["contactdetails"] = [];
            $pendingData = array();
            if ($sub == "save") {
                // check_token();
                try {
                    $sel = NULL;
                    if ($request->has("sel")) {
                        $sel = $request->input("sel");
                        if (!is_array($sel)) {
                            $sel = NULL;
                        }
                    }
                    $result = $domains->saveContactDetails($legacyClient, $request->input("contactdetails") ?? [], $request->input("wc") ?? [], $sel);
                    $contactdetails = $result["contactDetails"];
                    if ($result["status"] == "pending") {
                        $smartyvalues["pending"] = true;
                        if (!empty($result["pendingData"])) {
                            $pendingData = $result["pendingData"];
                        }
                    } else {
                        $smartyvalues["successful"] = true;
                    }
                } catch (\Exception $e) {
                    $smartyvalues["error"] = $e->getMessage();
                }
            }
            $success = $domains->moduleCall("GetContactDetails");
            if ($success) {
                if ($sub == "save" && $smartyvalues["successful"] === false && isset($contactdetails)) {
                    $contactDetails = $contactdetails;
                } else {
                    $contactDetails = $domains->getModuleReturn();
                }
                $contactTranslations = array();
                foreach ($contactDetails as $contactType) {
                    foreach (array_keys($contactType) as $contactFieldName) {
                        if (\Lang::get("client.domaincontactdetails" . $contactFieldName) == "domaincontactdetails" . $contactFieldName) {
                            $contactTranslations[$contactFieldName] = \Lang::get("client.domaincontactdetails" . $contactFieldName);
                        } else {
                            $contactTranslations[$contactFieldName] = $contactFieldName;
                        }
                    }
                }
                $templateContactDetails = $contactDetails;
                unset($templateContactDetails["domain"]);
                foreach ($templateContactDetails as &$contactData) {
                    (new \App\Helpers\Domains)->normaliseInternationalPhoneNumberFormat($contactData);
                    if (isset($contactData["Phone Country Code"])) {
                        unset($contactData["Phone Country Code"]);
                    }
                    foreach ($contactData as &$value) {
                        $value = \App\Helpers\Sanitize::encode($value);
                    }
                    unset($value);
                }
                unset($contactData);
                $smartyvalues["contactdetails"] = $templateContactDetails;
                $smartyvalues["contactdetailstranslations"] = $contactTranslations;
                try {
                    $domainInformation = $domains->getDomainInformation();
                } catch (\Exception $e) {
                }
                $smartyvalues["domainInformation"] = $domainInformation;
                $smartyvalues["irtpFields"] = array();
                if ($domainInformation instanceof \App\Helpers\Domain\Registrar\Domain && $domainInformation->isIrtpEnabled()) {
                    $smartyvalues["irtpFields"] = $domainInformation->getIrtpVerificationTriggerFields();
                }
                if ($domainInformation instanceof \App\Helpers\Domain\Registrar\Domain && $smartyvalues["pending"]) {
                    $message = "domainschangePending";
                    $replacement = array(":email" => $domainInformation->getRegistrantEmailAddress());
                    if ($domainInformation->getDomainContactChangeExpiryDate()) {
                        $message = "domainschangePendingDate";
                        $replacement["days"] = $domainInformation->getDomainContactChangeExpiryDate()->diffInDays();
                    }
                    if (!empty($pendingData)) {
                        $message = $pendingData["message"];
                        $replacement = $pendingData["replacement"];
                    }
                    $smartyvalues["pendingMessage"] = \Lang::get("client." . $message, $replacement);
                }
            } else {
                $smartyvalues["error"] = $domains->getLastError();
            }
            $smartyvalues["domainid"] = $domains->getData("id");
            $smartyvalues["domain"] = $domains->getData("domain");
            $smartyvalues["contacts"] = $legacyClient->getContactsWithAddresses();
            // TODO $ca->addOutputHookFunction("ClientAreaPageDomainContacts");
        }

        return view($templatefile, $smartyvalues);
    }

    public function Domain_Nameservers_Update(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $userid = $auth->id;
        $domainId = $request->domainid;
        $nschoice = $request->nschoice;
        $dataDomains = Domain::where('id', $domainId)->where('userid', $userid)->pluck('registrar', 'domain')->toArray();

        $registrarModule = new \App\Module\Registrar();

        foreach ($dataDomains as $d => $r) {
            $domain = $d;
            $registrar = $r;
        }

        $domainparts = explode(".", $domain, 2);
        $params = array();
        $params['clientid'] = $userid;
        $params['domainid'] = $domainId;
        $params['registrar'] = $registrar;
        $params['domainid'] = $domainId;
        list($params['sld'], $params['tld']) = $domainparts;

        if ($nschoice == "default") {
            $params = $registrarModule->RegGetDefaultNameservers($params, $domain);
        } else {
            $params["ns1"] = $request->ns1;
            $params["ns2"] = $request->ns2;
            $params["ns3"] = $request->ns3 ?? '';
            $params["ns4"] = $request->ns4 ?? '';
            $params["ns5"] = $request->ns5 ?? '';
        }

        $domains = Domain::select('status')->where('id', $domainId)->first();

        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.index')->with('warning', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                $registrarModule->RegSaveNameservers($params);
                return redirect()->route('pages.domain.mydomains.index')->with('success', __('client.domainregisternsmodsuccess'));
            }
        } else {
            return redirect()->route('pages.domain.mydomains.index')->with('error', __('client.domaincannotbemanaged'));
        }
    }

    public function DomainStatJson(Request $request)
    {
        $domainid = $request->domainid;
        $getDomain = Domain::where("id", $domainid)->orderBy("id", "DESC")->first();
        $autorenewStats = $getDomain->donotrenew;

        if ($autorenewStats == 1) {
            $statsStr = "Active";
        } else {
            $statsStr = "Inactive";
        }

        return response()->json($statsStr);
    }

    public function Domain_AutoRenew_Update(Request $request)
    {
        $id = $request->domainid;
        $status = $request->domainStatusChange;
        $getDomain = Domain::findOrFail($id);
        $getDomain->donotrenew = (int) $status;
        $getDomain->save();

        $newAutoRenewStatus = $getDomain->donotrenew;
        if ($newAutoRenewStatus == 1) {
            return redirect()->route('pages.domain.mydomains.index')->with('success', __('client.domainautorenewinfo'));
        } else {
            return redirect()->route('pages.domain.mydomains.index')->with('warning', __('client.domainsautorenewdisabledwarning'));
        }
    }

    public function Domain_Childnameservers(Request $request)
    {

        $auth = Auth::user();
        $userid = $auth->id;
        $domainid = $request->query('id');

        $domains = new Domains();
        $domain_data = $domains->getDomainsDatabyID($domainid);

        $domains = Domain::select('status')->where('id', $domainid)->first();

        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.index')->with('error', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                return view('pages.domain.mydomains.childnameservers', [
                    'domain_data' => $domain_data,
                ]);
            }
        } else {
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])->with('error', __('client.domaincannotbemanaged'));
        }
    }

    public function Domain_Childnameservers_Create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nameserver' => 'required|string',
            'ipAddress' => 'required|ip',
        ], [
            'ipAddress.ip' => 'The IP address provided is not valid.',
            'nameserver.required' => 'The nameserver field is required.',
            'ipAddress.required' => 'The IP address field is required.',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        $auth = Auth::guard('web')->user();
        $userid = $auth->id;
        $domainId = $request->input('domainidCreate');

        $params = array();
        $params['clientid'] = $userid;
        $params['domainid'] = $domainId;
        $params['registrar'] = $request->input('registrarCreate');
        $params['domain'] = $request->input('domainCreate');
        $params['host'] = $request->input('nameserver');
        $params['ip_address'] = $request->input('ipAddress');

        $domains = Domain::select('status')->where('id', $domainId)->first();

        $registrarModule = new \App\Module\Registrar();


        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainId])
                    ->with('error', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                $response = $registrarModule->RegSaveHost($params);
                $createResponse = json_decode($response->getContent(), true);

                if ($createResponse['code'] === 200) {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainId])
                        ->with('success', __('client.nameserverschildsuccess'));
                } else {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainId])
                        ->with('error', __('client.nameserverschildfailed'));
                }
            }
        } else {
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainId])
                ->with('error', __('client.domaincannotbemanaged'));
        }
    }

    public function Domain_Childnameservers_Delete(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'deleteNameserver' => 'required|string',
        ], [
            'deleteNameserver.required' => 'The nameserver field is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        $domainid = $request->input('domainidDelete');

        $domains = new Domains();
        $domain_data = $domains->getDomainsDatabyID($domainid);
        $host = $request->input('deleteNameserver');
        $currentDomain = $domain_data['domain'];

        $params = array();
        $params = [
            'clientid' => $domain_data['userid'],
            'domainid' => $domain_data['id'],
            'registrar' => $domain_data['registrar'],
            'domain' => $domain_data['domain'],
            'host' => $request->input('deleteNameserver'),
        ];

        $nameserver_request = $host . "." . $currentDomain;
        $nameserver_list = $this->getNameserverList($params);

        if (isset($nameserver_list['error']) && empty($nameserver_list['error'])) {
            // Error response with no specific error details
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', 'delete request failed');
        };

        if (empty($nameserver_list['data'])) {
            // Error nameserver not found
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', 'Nameserver not found');
        };

        if (!empty($nameserver_list['data'])) {
            foreach ($nameserver_list['data'] as $nameserver) {
                if ($nameserver['nameserver'] === $nameserver_request) {
                    $nameserver_ids = $nameserver['nameserver_id'];
                }
            }
        }

        $params['nsid'] = $nameserver_ids ?? [];
        $domains = Domain::select('status')->where('id', $domainid)->first();

        $registrarModule = new \App\Module\Registrar();

        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                    ->with('error', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                $response = $registrarModule->RegDeleteHost($params);

                if ($response['data'] === true && $response['code'] === 200) {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                        ->with('success', __('client.nameserverschilddeletesuccess'));
                } elseif ($response['error']) {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                        ->with('error', $response['error']);
                } else {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                        ->with('error', __('client.nameserverschilddeletefailed'));
                }
                return $response;
            }
        } else {
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', __('client.domaincannotbemanaged'));
        }
    }

    public function Domain_Childnameservers_Update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'updateNameserver' => 'required|string',
            'currentIP' => 'required|ip',
            'newIP' => 'required|ip',
        ], [
            'updateNameserver.required' => 'The nameserver field is required.',
            'currentIP.ip' => 'The IP address provided is not valid.',
            'currentIP.required' => 'The IP address field is required.',
            'newIP.ip' => 'The IP address provided is not valid.',
            'newIP.required' => 'The IP address field is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        $domainid = $request->input('domainidUpdate');

        $domains = new Domains();
        $domain_data = $domains->getDomainsDatabyID($domainid);
        $host = $request->input('updateNameserver');
        $currentDomain = $domain_data['domain'];

        $params = array();
        $params = [
            'clientid' => $domain_data['userid'],
            'domainid' => $domain_data['id'],
            'registrar' => $domain_data['registrar'],
            'domain' => $domain_data['domain'],
            'host' => $request->input('updateNameserver'),
            'ip_address' => $request->input('newIP')
        ];

        $nameserver_request = $host . "." . $currentDomain;
        $nameserver_list = $this->getNameserverList($params);

        if (isset($nameserver_list['error']) && empty($nameserver_list['error'])) {
            // Error response with no specific error details
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', 'delete request failed');
        };

        if (empty($nameserver_list['data'])) {
            // Error nameserver not found
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', 'Nameserver not found');
        };

        if (!empty($nameserver_list['data'])) {
            foreach ($nameserver_list['data'] as $nameserver) {
                if ($nameserver['nameserver'] === $nameserver_request) {
                    $nameserver_ids = $nameserver['nameserver_id'];
                }
            }
        }

        $params['nsid'] = $nameserver_ids ?? [];
        $domains = Domain::select('status')->where('id', $domainid)->first();

        $registrarModule = new \App\Module\Registrar();

        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                    ->with('error', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                $response = $registrarModule->RegDeleteHost($params);

                if ($response['data'] === true && $response['code'] === 200) {
                    $create = $registrarModule->RegSaveHost($params);
                    $createResponse = json_decode($create->getContent(), true);

                    if ($createResponse['code'] === 200) {
                        return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                            ->with('success', __('client.nameserverschildupdatesuccess'));
                    } else {
                        return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                            ->with('error', __('client.nameserverschildupdatedfailed'));
                    }
                } else {
                    return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                        ->with('error', __('client.domaincannotbemanaged'));
                }
            }
        } else {
            return redirect()->route('pages.domain.mydomains.childnameservers', ['id' => $domainid])
                ->with('error', __('client.domaincannotbemanaged'));
        }
    }

    private function getNameserverList($params)
    {

        $registrarModule = new \App\Module\Registrar();
        $response = $registrarModule->RegGetHost($params);

        return json_decode($response->getContent(), true);
    }

    public function Domain_Details(Request $request)
    {

        $auth = Auth::user();
        $userid = $auth->id;
        $domainid = $request->query('id');

        $domains = new Domains();
        $domain_data = $domains->getDomainsDatabyID($domainid);
        // $domain_status = $domain_data['status'];

        $domains = Domain::select('status')->where('id', $domainid)->first();

        if ($domains) {
            if ($domains->status !== "Active") {
                return redirect()->route('pages.domain.mydomains.index')->with('error', __('client.domainCannotBeManagedUnlessActive'));
            } else {
                return view('pages.domain.mydomains.details', [
                    'domain_data' => $domain_data,
                ]);
            }
        } else {
            return redirect()->route('pages.domain.mydomains.details', ['id' => $domainid])->with('error', __('client.domaincannotbemanaged'));
        }
    }


    // Domain Document View
    // Author : Anggi
    // Last Updated : 11/11/2024
    public function Domain_Document_Upload(Request $request)
    {

        $auth = Auth::user();
        $userid = $auth->id;
        $module = $request->query('module');
        $action = 'clientHome';

        $params = array();
        $params['userid'] = $userid;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while calling the module.']);
        }

        return view('pages.domain.mydomains.documentdomain', [
            'userid'   => $result['data']['userid'],
            'domains'  => $result['data']['domains'],
            'document' => $result['data']['document'],
            'dir'      => $result['data']['dir'],
        ]);
    }

    // Requirement Domain Document View
    // Author : Anggi
    // Last Updated : 14/11/2024
    public function Domain_Document_Requirement(Request $request)
    {
        $auth = Auth::user();
        $userid = $auth->id;
        $module = $request->query('module');
        $action = 'requirement';

        $params = array();
        $params['userid'] = $userid;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while calling the module.']);
        }

        return view('pages.domain.mydomains.documentrequirement', [
            'id'       => $result['data']['id'],
            'domains'  => $result['data']['domains'],
            'document' => $result['data']['document'],
            'dir'      => $result['data']['dir'],
            'table'    => $result['data']['table'],
        ]);
    }


    // Upload Document
    // Author : Anggi
    // Last Updated : 11/11/2024
    public function uploadDocuments(Request $request)
    {

        $auth = Auth::user();
        $userid = $auth->id;

        $validator = Validator::make($request->all(), [
            'upload_file'   => "required|mimes:pdf,jpg,jpeg,png|max:2000",
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('pages.domain.mydomains.details.document', ['id' => $userid])
                ->withErrors($validator)
                ->withInput()
                ->with('type', 'danger')
                ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $module = 'PrivateNsRegistrar';
        $action = 'uploadImage';

        $params = array();
        $params['file'] = $request->file('upload_file');
        $params['userid'] =  $userid;
        $domains = new \App\Helpers\DomainsClass();
        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    // Upload List Document
    // Author : Anggi
    // Last Updated : 11/11/2024
    public function updateListDocuments($userid)
    {
        $clientFiles = Clientsfile::where("userid", $userid)->orderBy("title", "ASC")->get()->toArray();
        return response()->json($clientFiles);
    }


    // Delete File in Storage
    // Author : Anggi
    // Last Updated : 12/11/2024
    public function deleteFile(Request $request)
    {

        $domainId = $request->query('id');
        $fileName = $request->input('fileName');

        $validator = Validator::make($request->all(), [
            'fileName'   => "required",
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('pages.domain.mydomains.details.document', ['id' => $domainId])
                ->withErrors($validator)
                ->withInput()
                ->with('type', 'danger')
                ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $module = 'PrivateNsRegistrar';
        $action = 'deleteImage';

        $params = array();
        $params['file'] = $fileName;
        $domains = new \App\Helpers\DomainsClass();
        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function Domain_Document_Requirement_Detail(Request $request)
    {

        $auth = Auth::user();
        $userid = $auth->id;
        $module = 'PrivateNsRegistrar';
        $action = 'domainDetail';

        $params = array();
        $params['token'] = $request->input('_token');
        $params['domain'] = $request->input('domain');
        $params['userid'] = $request->input('userid');

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while calling the module.']);
        }

        return $result;
    }

    public function callModulePrivate(Request $request)
    {
        $module = $request->query('module');
        $action = $request->query('action');

        $params = array();
        $params['clientid'] = 1;
        $params['domainid'] = 1;
        $params['registrar'] = 'Irsfa';

        $domains = new \App\Helpers\DomainsClass();
        $result = $domains->callModuleAddon($module, $action, $params);

        return $result;
    }

    /*
  * For Show View DNS Manager
  * Author: Fajar Habib Zaelani
  * Last Updated: 19 November 2023
  */
    public function dnsManager(Request $request)
    {
        $auth = Auth::user();
        $domainId = $request->query('id');
        $domains = new Domains();
        $domainData = $domains->getDomainsDatabyID($domainId);

        $domain = $domainData["domain"];
        $uid = $auth->id;
        $dnsmanager = new DNSManagerHelper();

        if (!boolval($domain)) {
            throw new Exception("Domain not found.");
        }

        $cekdomain = Domain::where('status', 'Active')->where('domain', $domain)->where('userid', $uid)->first();

        if (!$cekdomain) {
            throw new \Exception("Domain not found or invalid permission.");
        }

        $domainclient = Domain::where('status', 'Active')->where('userid', $uid)->get();

        $res = $dnsmanager->listAddons($domain);
        $res_decoded = json_decode($res);
        $cekaddons = $res_decoded->cpanelresult->data;

        if (empty($res['data']['cpanelresult']['data'])) {
            $enabled = false;
        } else {
            $enabled = true;
        }

        return view('pages.domain.mydomains.forwarddomain.dns-manager', [
            'id'     => $uid,
            'domains' => $domainclient,
            'domainId' => $domainId,
            'domain' => $domain,
            'enabled' => $enabled
        ]);
    }

    public function tldLookup(Request $request)
    {

        $userId = $request->input('userid');
        $domain = $request->input('domain');
        $token = $request->input('_token');

        $module = 'PrivateNsRegistrar';
        $action = 'lookupTld';

        $params = array();
        $params['userid'] = $userId;
        $params['domain'] = $domain;
        $params['token'] = $token;

        $domains = new \App\Helpers\DomainsClass();
        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function setDocument(Request $request)
    {

        $domain = $request->input('domain');
        $file = $request->input('file');
        $type = $request->input('type');
        $setAll = $request->input('setAll');

        $module = 'PrivateNsRegistrar';
        $action = 'setDocument';

        $params = array();
        $params['domain'] = $domain;
        $params['file'] = $file;
        $params['type'] = $type;
        $params['setAll'] = $setAll;

        $domains = new \App\Helpers\DomainsClass();
        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    
    public function DnsListRecords(Request $request)
    {

        $domain = $request->input('domain');

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['command'] = 'listrecord';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;

        // $enabled = true;
        // $id = 1;
        // $domains = [
        //     (object)['domain' => 'example.com'],
        //     (object)['domain' => 'anotherdomain.com'],
        // ];

        // return view('pages.domain.mydomains.forwarddomain.dnsmanager', [
        //     'domain' => $domain,
        //     'enabled' => $enabled,
        //     'id' => $id,
        //     'domains' => $domains,
        // ]);

    }

    public function createDns(Request $request)
    {
        $domain = $request->input('domain');

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['command'] = 'createdns';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function forwardDomain(Request $request)
    {
        $auth = Auth::user();
        $domainId = $request->query('id');
        $domains = new Domains();
        $domainData = $domains->getDomainsDatabyID($domainId);

        $domain = $domainData["domain"];
        $uid = $auth->id;
        $dnsmanager = new DNSManagerHelper();

        if (!boolval($domain)) {
            throw new Exception("Domain not found.");
        }

        $cekdomain = Domain::where('status', 'Active')->where('domain', $domain)->where('userid', $uid)->first();

        if (!$cekdomain) {
            throw new \Exception("Domain not found or invalid permission.");
        }

        $domainclient = Domain::where('status', 'Active')->where('userid', $uid)->get();

        $res = $dnsmanager->listAddons($domain);
        $res_decoded = json_decode($res);
        $cekaddons = $res_decoded->cpanelresult->data;

        if (empty($res['data']['cpanelresult']['data'])) {
            $enabled = false;
        } else {
            $enabled = true;
        }

        return view('pages.domain.mydomains.forwarddomain.forward-domain', [
            'id'     => $uid,
            'domains' => $domainclient,
            'domainId' => $domainId,
            'domain' => $domain,
            'enabled' => $enabled
        ]);
    }

    public function forwardEmail(Request $request)
    {
        $auth = Auth::user();
        $domainId = $request->query('id');
        $domains = new Domains();
        $domainData = $domains->getDomainsDatabyID($domainId);

        $domain = $domainData["domain"];
        $uid = $auth->id;
        $dnsmanager = new DNSManagerHelper();

        // if (!boolval($domain)){
        //     throw new Exception("Domain not found.");
        // }

        // $cekdomain = Domain::where('status', 'Active')->where('domain', $domain)->where('userid', $uid)->first();

        // if (!$cekdomain) {
        //     throw new \Exception("Domain not found or invalid permission.");
        // }

        // $domainclient = Domain::where('status', 'Active')->where('userid', $uid)->get();

        // $res = $dnsmanager->listAddons($domain);
        // $res_decoded = json_decode($res);
        // $cekaddons = $res_decoded->cpanelresult->data;

        // if (empty($res['data']['cpanelresult']['data'])){
        //     $enabled = false;
        // } else {
        //     $enabled = true;
        // }


        $enabled = true; // Dummy data
        $domain = 'example.com'; // Dummy data
        $email = 'example@mail.com'; // Dummy data
        $datas = [
            ['id' => 1, 'alias' => 'info', 'email' => 'info@mail.com'],
            ['id' => 2, 'alias' => 'support', 'email' => 'support@mail.com'],
        ]; // Dummy data

        return view('pages.domain.mydomains.forwarddomain.forward-email', [
            'domainId' => $domainId,
            'enabled' => $enabled,
            'domain' => $domain,
            'email' => $email,
            'datas' => $datas,
        ]);
    }

    public function addForwardDomain(Request $request)
    {

        $domain = $request->input('domain');
        $redirect = $request->input('redirect');
        $masked = $request->input('masked');

        if ($masked == false) {
            $forwardCommand = 'forwarddomain';
        } else {
            $forwardCommand = 'maskdomain';
        }

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = [
            'domain' => $domain,
            'redirect' => $redirect,
            'command' => $forwardCommand,
        ];

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }

    public function removeForwardDomain(Request $request)
    {

        $domain = $request->input('domain');
        $redirect = $request->input('redirect');

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = [
            'domain' => $domain,
            'redirect' => $redirect,
            'command' => 'removeforward',
        ];

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }

    public function forward_domain_clientarea(Request $request)
    {
        // try {
            $auth = Auth::user();
            $page =  $request->query('page');
            $domainId = $request->query('id');

            $domainHelper = new Domains();
            $domainData = $domainHelper->getDomainsDatabyID($domainId);
            $domain = $domainData['domain'];
            $uid = $auth->id;
            $dnsmanager = new DNSManagerHelper();

            if (!boolval($domain)) {
                throw new Exception("Domain not found.");
            }

            $cekdomain = Domain::where('status', 'Active')->where('domain', $domain)->where('userid', $uid)->first();

            if (!$cekdomain) {
                throw new \Exception("Domain not found or invalid permission.");
            }

            // $whoisHelper = new Domains();
            // $whoisResult = $whoisHelper->DomainWhois($domain);
            // $resultsRaw = $whoisResult['whois'];

            // $lines = explode("\n", $resultsRaw);
            // $results = [];
            // $nsCount = 1;

            // foreach ($lines as $line) {
            //     $line = trim($line); 
            //     if (stripos($line, 'Name Server:') === 0) {
            //         $parts = explode(':', $line, 2); 
            //         if (isset($parts[1])) {
            //             $results["ns$nsCount"] = strtolower(trim(strip_tags($parts[1]))); 
            //             $nsCount++;
            //         }
            //     }
            // }

            // $nsqwords = ['dnsiix1.qwords.net', 'dnsiix2.qwords.net'];
            // $matchFound = false;

            // foreach ($nsqwords as $nsqword) {
            //     if (
            //         $nsqword === ($results['ns1'] ?? '') || 
            //         $nsqword === ($results['ns2'] ?? '') || 
            //         $nsqword === ($results['ns3'] ?? '') || 
            //         $nsqword === ($results['ns4'] ?? '')
            //     ) {
            //         $matchFound = true;
            //         break;
            //     }
            // }

            // if (!$matchFound) {
            //     return view('pages.domain.mydomains.forwarddomain.warning', [
            //         'domainid' => $domainId,
            //     ]);
            // }

            $domainClient = Domain::where('status', 'Active')
                ->where('userid', $uid)
                ->get();

            $type = $dnsmanager->listType();
            $type = is_array($type) ? $type : json_decode($type, true);
            $typeNames = collect($type)->pluck('name')->all();
            $records = $dnsmanager->listRecords($domain);
            $cekRecords = $records['data']['cpanelresult']['data'];
            $cekRecordsResult = $dnsmanager->listRecordsWHM($domain);
            $cekRecordsWhm = $cekRecordsResult['data']['data']['zone'][0]['record'];

            if (empty($records['data']['cpanelresult']['data']) && empty($cekRecordsResult['data']['data']['zone'][0]['record'])) {
                return view('pages.domain.mydomains.forwarddomain.init', [
                    'id'     => $uid,
                    'domains' => $domainClient,
                    'records' => $records,
                    'domain' => $domain,
                    'domainId' => $domainId,
                    'type' => $type,
                ]);
            }

            $records = $cekRecordsWhm;

            $collection = collect($records);
            $filtered = $collection->filter(function ($value, $key) use ($typeNames) {
                return in_array($value->type, $typeNames);
            });
            $records = $filtered->all();
            $collection = collect($records);
            $sorted = $collection->sortBy('type');
            $records = $sorted->values()->all();
            $res = $dnsmanager->listAddons($domain);
            $cekaddons = $res['data']['cpanelresult']['data'];

            if (empty($cekaddons)) {
                $enabled = false;
            } else {
                $enabled = true;
            }

            if ($page == 'dns') {
                $records = array_filter($records, function ($val) {
                    return $val->type !== 'NS';
                });
                return view('pages.domain.mydomains.forwarddomain.dns-manager', [
                    'id'        => $uid,
                    'domainId'  => $domainId,
                    'domains'   => $domainClient,
                    'records'   => $records,
                    'domain'    => $domain,
                    'type'      => $type,
                ]);
            } elseif ($page == 'domain_fwd') {
                return view('pages.domain.mydomains.forwarddomain.forward-domain', [
                    'id'        => $uid,
                    'domainId'  => $domainId,
                    'domains'   => $domainClient,
                    'domain'    => $domain,
                    'enabled'   => $enabled
                ]);
            } elseif ($page == 'email_fwd') {

                $email = $dnsmanager->getDBEmail($domain);
                $email =json_decode($email);
                $emails = $dnsmanager->getDBEmails($domain);
                $emails =json_decode($emails);
                
                return view('pages.domain.mydomains.forwarddomain.forward-email', [
                    'id'     => $uid,
                    'domainId'  => $domainId,
                    'domains' => $domainClient,
                    'records' => $records,
                    'domain' => $domain,
                    'enabled' => $enabled,
                    'email' => $email->email,
                    'datas' => $emails->data
                ]);
                
            }
        // } catch (\Exception $e) {
        //     return view('pages.domain.mydomains.forwarddomain.error', [
        //         'error' => $e->getMessage()
        //     ]);
        // }
    }

    public function addRecordWHM(Request $request)
    {

        $domain = $request->input('domain');
        $name = $request->input('name');
        $type = $request->input('type');
        $ttl = $request->input('ttl');
        $values = $request->input('values');
        $command = $request->input('action');

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['name'] = $name;
        $params['type'] = $type;
        $params['ttl'] = $ttl;
        $params['values'] = $values;
        $params['command'] =  $command;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function deleteRecordWHM(Request $request)
    {

        $domain = $request->input('domain');
        $line = $request->input('line');
        $command = 'deleterecordwhm';

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['line'] = $line;
        $params['command'] = $command;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function editRecordWHM(Request $request)
    {

        $domain = $request->input('domain');
        $line = $request->input('line');
        $name = $request->input('name');
        $ttl = $request->input('ttl');
        $type = $request->input('type');
        $values = $request->input('values');

        $command = 'editrecordwhm';
        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['line'] = $line;
        $params['name'] = $name;
        $params['ttl'] = $ttl;
        $params['type'] = $type;
        $params['values'] = $values;
        $params['command'] = $command;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function resetRecordWHM(Request $request)
    {

        $domain = $request->input('domain');

        $command = 'resetrecordwhm';
        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['command'] = $command;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function deletednsRecordWHM(Request $request)
    {
        $domain = $request->input('domain');

        $command = 'deletednsrecordwhm';
        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['command'] = $command;

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
        }

        return $result;
    }

    public function addForwardEmail(Request $request)
    {
        $auth = Auth::user();
        $domain = $request->input('domain');
        $alias = $request->input('alias');
        $email = $request->input('email');
        $uid = $auth->id;

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['alias'] = $alias;
        $params['email'] = $email;
        $params['uid'] = $uid;
        $params['command'] = 'addforwardemail';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }

    public function removeForwardEmail(Request $request)
    {
        $auth = Auth::user();
        $domain = $request->input('domain');
        $alias = $request->input('alias');
        $email = $request->input('email');
        $id = $request->input('id');

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['alias'] = $alias;
        $params['email'] = $email;
        $params['id'] = $id;
        $params['command'] = 'removeforwardemail';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }

    public function setForwardEmail(Request $request)
    {
        $auth = Auth::user();
        $domain = $request->input('domain');
        $email = $request->input('email');
        $uid = $auth->id;

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['email'] = $email;
        $params['uid'] = $uid;
        $params['command'] = 'settingforwardemail';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }

    public function unsetForwardEmail (Request $request)
    {
        $auth = Auth::user();
        $domain = $request->input('domain');
        $email = $request->input('email');
        $uid = $auth->id;

        $module = 'ForwardDomain';
        $action = 'handleAction';

        $params = array();
        $params['domain'] = $domain;
        $params['email'] = $email;
        $params['uid'] = $uid;
        $params['command'] = 'unsetforwardemail';

        $domains = new \App\Helpers\DomainsClass();

        try {
            $result = $domains->callModuleAddon($module, $action, $params);
        } catch (\Exception $e) {
            \Log::error('Error in callModuleAddon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the request.',
            ], 500);
        }

        return $result;

    }
}
