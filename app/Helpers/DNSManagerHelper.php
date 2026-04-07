<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\ForwardDomain;
use App\Models\ForwardEmail;
use App\Helpers\Domains;

// require_once $_SERVER['DOCUMENT_ROOT'] . '/init.php';

class DNSManagerHelper
{
    private $base_url;
    private $host;



    function __construct($host = "http://dnsmanager.my.id/")
    {
        $this->host = $host;
        $this->base_url = $host . "domain-forward/forward-domain.php";
    }

    public function writeLog($str)
    {
        $log_string = date("F j, Y, g:i a") . ' -- ' . $str . PHP_EOL;
        file_put_contents('./logs/log_-' . date("j.n.Y") . '.log', $log_string, FILE_APPEND);
    }

    public function runQuery($query)
    {
        try {
            $response = Http::get($query);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'data' => $response->json(),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'HTTP request failed',
                'details' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function validateDomain($domain)
    {
        try {
            $id = Auth::id();
            $cekdomain = Domain::where('status', 'Active')
                ->where('domain', $domain)
                ->where('userid', $id)
                ->first();

            if (!$cekdomain) {
                throw new \Exception("Domain not found or invalid permission.");
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function listRecords($domain)
    {
        $input = [
            "action" => "listrecord",
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function listAddons($domain)
    {
        $input = [
            "action" => "listaddons",
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function listType()
    {
        $input = [
            "action" => "listtype",
        ];
        $qs = '?' . http_build_query($input);
        return $this->runQuery($this->base_url . $qs);
    }

    public function createDNSManager($domain)
    {
        $input = [
            "action" => "createdns",
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function validateNameserver($domain)
    {
        $helper = new Domains();
        $results = $helper->DomainWhois($domain);

        if (!isset($results['whois'])) {
            return [false, "Unable to fetch WHOIS data"];
        }

        $whoisData = $results['whois'];

        $pattern = '/(?<=Name Server:)[^<]+/m';
        preg_match_all($pattern, $whoisData, $matches);

        $haystack = [];
        foreach ($matches as $match) {
            $haystack = array_merge($haystack, $match);
        }
        $haystack = array_map('trim', $haystack);
        $target = [
            'dns1.qwords.id',
            'dns2.qwords.id',
            'dnsiix1.qwords.net',
            'dnsiix2.qwords.net',
        ];

        foreach ($haystack as $nameserver) {
            if (in_array($nameserver, $target)) {
                return [true];
            }
        }

        return [false, "Nameserver not using Qwords", $haystack];
    }

    public function validateHosting($domain)
    {
        // Query the Hosting model for active or suspended domains
        $hostingRecords = Hosting::where('domain', $domain)
            ->whereIn('domainstatus', ['Active', 'Suspended'])
            ->get();

        // Check if any hosting records are found
        if ($hostingRecords->isEmpty()) {
            return [true];
        } else {
            return [false, "Domain used in hosting"];
        }
    }

    public function queryDBForwardDomain($action, $domain, $redirect, $masked = 'false')
    {
        $uid = auth()->id();

        if ($action === 'add') {
            $exists = ForwardDomain::where('domain', $domain)->first();

            if (!$exists) {
                $fwdDomain = ForwardDomain::create([
                    'client_id' => $uid,
                    'domain' => $domain,
                    'target' => $redirect,
                    'isMasked' => $masked,
                ]);
            } else {
                $fwdDomain = ForwardDomain::where('domain', $domain)
                    ->update([
                        'target' => $redirect,
                        'isMasked' => $masked,
                    ]);
            }
        } elseif ($action === 'remove') {
            $fwdDomain = ForwardDomain::where('domain', $domain)->delete();
        }

        return $fwdDomain;
    }

    public function forwardDomain($domain, $target)
    {
        $v1 = $this->validateDomain($domain);
        $v2 = $this->validateHosting($domain);
        // $v3 = $this->validateNameserver($domain);

        // if ($v1 && $v2[0] && $v3[0]) {
        if ($v1 && $v2[0]) {

            $input = [
                "action" => "forwarddomain",
                "domain" => $domain,
                "target" => $target,
            ];
            $qs = '?' . http_build_query($input);
            $run =  $this->runQuery($this->base_url . $qs);

            $this->queryDBForwardDomain("add", $domain, $target, 'false');
            return $run;
        } else {
            return [
                // "errorgg" => $v1 == false ? "domain not found or invalid permission" : $v2[1] . '--' . $v3[1]
                "errorgg" => $v1 == false ? "domain not found or invalid permission" : $v2[1] . '--'

            ];
        }
    }

    public function maskDomain($domain, $target)
    {
        $v1 = $this->validateDomain($domain);
        $v2 = $this->validateHosting($domain);
        // $v3 = $this->validateNameserver($domain);

        // if ($v1 && $v2[0] && $v3[0]) {
        if ($v1 && $v2[0]) {
            $input = [
                "action" => "maskdomain",
                "domain" => $domain,
                "target" => $target,
            ];
            $qs = '?' . http_build_query($input);
            $run =  $this->runQuery($this->base_url . $qs);
            $this->queryDBForwardDomain("add", $domain, $target, 'true');
            return $run;
        } else {
            return [
                // "errorgg" => $v1 == false ? "domain not found or invalid permission" : $v2[1] . '--' . $v3[1]
                "errorgg" => $v1 == false ? "domain not found or invalid permission" : $v2[1]
            ];
        }
    }

    public function listRecordsWHM($domain)
    {
        $input = [
            "action" => "listrecord_whm",
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function getDBEmail($domain)
    {
        $valid = $this->validateDomain($domain);
        if ($valid) {
            $data = ForwardEmail::where('domain', $domain)->first();
            return json_encode(["status" => "ok", "email" => $data->email]);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function getDBEmails($domain)
    {
        $valid = $this->validateDomain($domain);
        if ($valid) {
            $data = ForwardEmail::where('domain', $domain)->get();
            return json_encode(["status" => "ok", "data" => $data]);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function addRecordsWHM($domain, $name, $type, $ttl, $values)
    {
        $input = [
            "action" => "addrecord_whm",
            "domain" => $domain,
            "name" => $name,
            "type" => $type,
            "ttl" => $ttl,
            "values" => array_filter($values, function ($value) {
                return !is_null($value) && $value !== '';
            }) // remove null values
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function deleteRecordWHM($domain, $line)
    {
        $input = [
            "action" => "deleterecord_whm",
            "domain" => $domain,
            "line"   => $line
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function editRecordWHM($domain, $name, $type, $line, $ttl, $values)
    {
        $input = [
            "action" => "editrecord_whm",
            "domain" => $domain,
            "name" => rtrim($name, '.'),
            "type" => $type,
            "line" => $line,
            "ttl" => $ttl,
            "values" => array_filter($values, function ($value) {
                return !is_null($value) && $value !== '';
            }) // remove null values
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function resetZoneWHM($domain)
    {
        $input = [
            "action" => "resetzone_whm",
            "domain" => $domain
        ];
        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function deleteDNSManagerWHM($domain)
    {
        $input = [
            "action" => "deletedns_whm",
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function removeForward($domain)
    {
        $v1 = $this->validateDomain($domain);
        $v2 = $this->validateHosting($domain);
        // $v3 = $this->validateNameserver($domain);

        // if ($v1 && $v2[0] && $v3[0]){
        if ($v1 && $v2[0]) {
            $input = [
                "action" => "removeforward",
                "domain" => $domain,
            ];
            $qs = '?' . http_build_query($input);
            $run = $this->runQuery($this->base_url . $qs);
            $this->queryDBForwardDomain("remove", $domain, null, null);
            return $run;
        } else {
            return [
                // "error" => $v1 == false ? "domain not found or invalid permission" : $v2[1] . '--' . $v3[1]
                "error" => $v1 == false ? "domain not found or invalid permission" : $v2[1]
            ];
        }
    }

    public function addForwardEmail($alias, $email, $domain)
    {
        $input = [
            "action" => "add_email_forward",
            "alias" => $alias,
            "email" => $email,
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function checkDBEmail($alias, $domain)
    {
        $data = ForwardEmail::where('domain', $domain)
            ->where('alias', $alias)
            ->first();

        return boolval($data);
    }
    
    public function insertDBEmail($uid, $alias, $email, $domain)
    {
        $valid = $this->validateDomain($domain);

        if ($valid) {
            $data = ForwardEmail::create([
                'uid' => $uid,
                'domain' => $domain,
                'email' => $email,
                'alias' => $alias,
            ]);

            return $data;
        } else {
            return response()->json(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function removeDBEmail($domain)
    {
        $valid = $this->validateDomain($domain);
        if ($valid) {
            $data = ForwardEmail::where('domain', $domain)->delete();
            return $data;
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Domain not valid'
            ]);
        }
    }

    public function removeForwardEmail($alias, $email, $domain)
    {
        $input = [
            "action" => "remove_email_forward",
            "alias" => $alias,
            "email" => $email,
            "domain" => $domain,
        ];

        $valid = $this->validateDomain($domain);
        if ($valid) {
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function saveDBEmail($uid, $email, $domain)
    {
        $valid = $this->validateDomain($domain);
        if ($valid) {
            $exists = ForwardEmail::where('domain', $domain)->first();

            if (!$exists) {
                $data = ForwardEmail::create([
                    'uid' => $uid,
                    'domain' => $domain,
                    'email' => $email,
                    'alias' => null,
                ]);
            } else {
                $data = $exists->update([
                    'email' => $email,
                    'alias' => null,
                ]);
            }

            return $data;
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Domain not valid',
            ]);
        }
    }

    public function setForwardEmail($email, $domain)
    {
        $input = [
            "action" => "set_email_forward",
            "email" => $email,
            "domain" => $domain,
        ];
        
        $valid = $this->validateDomain($domain);
        if ($valid){
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

    public function unsetForwardEmail($email, $domain)
    {
        $input = [
            "action" => "unset_email_forward",
            "email" => $email,
            "domain" => $domain,
        ];
        
        $valid = $this->validateDomain($domain);
        if ($valid){
            $qs = '?' . http_build_query($input);
            return $this->runQuery($this->base_url . $qs);
        } else {
            return json_encode(["status" => "error", "message" => "Domain not valid"]);
        }
    }

}
