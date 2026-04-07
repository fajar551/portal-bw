<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Helpers\LogActivity;

class WHOIS
{
    protected $definitions = [];
    protected $definitionsPath = null;
    protected $socketPrefix = "socket://";

    public function __construct($definitionsPath = "")
    {
        if (!empty($definitionsPath)) {
            $this->definitionsPath = $definitionsPath;
        }
        $this->loadDefinitions();
    }

    public function init()
    {
        // Initialization logic if needed
    }

    public function getSocketPrefix()
    {
        return $this->socketPrefix;
    }

    public function canLookup($tld)
    {
        return array_key_exists($tld, $this->definitions);
    }

    public function getFromDefinitions($tld, $key)
    {
        return isset($this->definitions[$tld][$key]) ? $this->definitions[$tld][$key] : "";
    }

    public function lookup($parts)
    {
        $sld = $parts["sld"];
        $tld = $parts["tld"];
        $idnConverter = new \App\Helpers\Domain\Idna();
        $encodedSld = $idnConverter->encode($sld);

        if ($encodedSld !== $sld) {
            if (\App\Helpers\Cfg::get('AllowIDNDomains')) {
                $sld = $encodedSld;
            } else {
                return false;
            }
        }

        try {
            $uri = $this->getUri($tld);
            $availableMatchString = $this->getAvailableMatchString($tld);
            $isSocketLookup = $this->isSocketLookup($tld);
        } catch (\Exception $e) {
            return false;
        }

        $fullDomain = $domain = $sld . $tld;
        $now = Carbon::now();
        $whosisLOG = new \App\Models\Whoislog();
        $whosisLOG->date = $now;
        $whosisLOG->domain = $fullDomain;
        $whosisLOG->ip = Request::ip();
        $whosisLOG->save();

        try {
            if ($isSocketLookup) {
                $uri = substr($uri, strlen($this->getSocketPrefix()));
                $port = 43;
                if (strpos($uri, ":")) {
                    list($uri, $port) = explode(":", $uri, 2);
                }
                $lookupResult = $this->socketWhoisLookup($domain, $uri, $port, $tld);
            } else {
                $lookupResult = $this->httpWhoisLookup($domain, $uri);
            }
        } catch (\Exception $e) {
            $results = ["result" => "error"];
            if (isset($_SESSION["adminid"])) {
                $results["errordetail"] = $e->getMessage();
            }
            return $results;
        }
        $lookupResult = " ---" . $lookupResult;
        $results = [];
        if (strpos($lookupResult, $availableMatchString) !== false) {
            $results["result"] = "available";
        } else {
            $results["result"] = "unavailable";
            $results["whois"] = $isSocketLookup ? nl2br(htmlentities($lookupResult)) : nl2br(htmlentities(strip_tags($lookupResult)));
            $response = $this->getEppStatus($lookupResult);
            $results["eppStatus"] = $response;
        }
        return $results;
    }

    private function loadDefinitions()
    {
        $path = public_path("/dist.whois.json");
        $overridePath = public_path("/whois.json");
        $this->definitions = array_merge($this->parseFile($path), $this->parseFile($overridePath));
    }

    private function parseFile($path)
    {
        $return = [];
        if (file_exists($path)) {
            $definitions = file_get_contents($path);
            if ($definitions = @json_decode($definitions, true)) {
                foreach ($definitions as $definition) {
                    $extensions = explode(",", $definition["extensions"]);
                    unset($definition["extensions"]);
                    foreach ($extensions as $extension) {
                        $return[$extension] = $definition;
                    }
                }
            } else {
                LogActivity::Save("Unable to load WHOIS Server Definition File: " . $path);
            }
        }
        return $return;
    }

    private function getUri($tld)
    {
        if ($this->canLookup($tld)) {
            $uri = $this->getFromDefinitions($tld, "uri");
            if (empty($uri)) {
                throw new \Exception("Uri not defined for whois service");
            }
            return $uri;
        }
        throw new \Exception("Whois server not known for " . $tld);
    }

    private function isSocketLookup($tld)
    {
        if ($this->canLookup($tld)) {
            $uri = $this->getUri($tld);
            return substr($uri, 0, strlen($this->getSocketPrefix())) == $this->getSocketPrefix();
        }
        throw new \Exception("Whois server not known for " . $tld);
    }

    private function getAvailableMatchString($tld)
    {
        if ($this->canLookup($tld)) {
            return $this->getFromDefinitions($tld, "available");
        }
        throw new \Exception("Whois server not known for " . $tld);
    }

    private function httpWhoisLookup($domain, $uri)
    {
        $url = $uri . $domain;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        if (curl_error($ch)) {
            curl_close($ch);
            throw new \Exception("Error: " . curl_errno($ch) . " - " . curl_error($ch));
        }
        curl_close($ch);
        return $data;
    }

    private function socketWhoisLookup($domain, $server, $port, $tld)
    {
        $fp = @fsockopen($server, $port, $errorNumber, $errorMessage, 10);
        if ($fp === false) {
            throw new \Exception("Error: " . $errorNumber . " - " . $errorMessage);
        }
        @fputs($fp, $domain . "\r\n");
        @socket_set_timeout($fp, 10);
        $data = "";
        while (!@feof($fp)) {
            $data .= @fread($fp, 4096);
        }
        @fclose($fp);
        return $data;
    }

    public function getEppStatus($whoisData)
    {
        $eppStatus = [];
        $eppStatusCodes = [
            'addPeriod', 'autoRenewPeriod', 'inactive', 'ok', 'pendingCreate', 
            'pendingDelete', 'pendingRenew', 'pendingRestore', 'pendingTransfer', 
            'pendingUpdate', 'redemptionPeriod', 'renewPeriod', 'serverDeleteProhibited', 
            'serverHold', 'serverRenewProhibited', 'serverTransferProhibited', 
            'serverUpdateProhibited', 'transferPeriod', 'clientTransferProhibited', 'clientHold'
        ];

        // If $whoisData is not an array, convert it to an array
        if (!is_array($whoisData)) {
            $whoisData = explode("\n", $whoisData);
        }

        // Process each line to find "Status:"
        foreach ($whoisData as $line) {
            // Ensure $line is a string
            if (is_array($line)) {
                $line = implode(" ", $line); // Convert array to string if necessary
            }

            if (strpos($line, 'Status:') === 0) {
                $status = trim(substr($line, strlen('Status:')));
                if (in_array($status, $eppStatusCodes)) {
                    $eppStatus[] = $status;
                }
            }
        }

        return $eppStatus;
    }
}