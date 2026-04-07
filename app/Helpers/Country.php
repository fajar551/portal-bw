<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\LogActivity;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class Country
{
	protected $countries = array();
    protected $countriesPath = NULL;

	public function __construct($countriesPath = "")
	{
		if (!empty($countriesPath)) {
            $this->countriesPath = $countriesPath;
        } else {
            $this->countriesPath = public_path();
        }
        $this->load();
	}

	protected function load()
    {
        $path = $this->countriesPath . "/dist.countries.json";
        // $overridePath = $this->countriesPath . "countries.json";
        // $countries = array_merge($this->loadFile($path), $this->loadFile($overridePath));
        $countries = array_merge($this->loadFile($path));
        foreach ($countries as $code => $data) {
            if (!$data) {
                unset($countries[$code]);
            }
        }
        $this->countries = $countries;
    }
    protected function loadFile($path)
    {
        $countries = array();
        if (file_exists($path)) {
            $countries = file_get_contents($path);
            $countries = json_decode($countries, true);
            if (!is_array($countries)) {
                LogActivity::Save("Unable to load Countries File: " . $path);
                $countries = array();
            }
        }
        return $countries;
    }
    public function getCountries()
    {
        return $this->countries;
    }
    public function getCountryNameArray()
    {
        $countries = array();
        foreach ($this->getCountries() as $code => $data) {
            $countries[$code] = $data["name"];
        }
        return $countries;
    }
    public function getCountryNamesOnly()
    {
        $countries = array();
        foreach ($this->getCountries() as $data) {
            $countries[$data["name"]] = $data["name"];
        }
        return $countries;
    }
    public function getCallingCode($countryCode)
    {
        $countries = $this->getCountries();
        if (array_key_exists($countryCode, $countries)) {
            return $countries[$countryCode]["callingCode"];
        }
        return 0;
    }
    public function getName($countryCode)
    {
        $countries = $this->getCountries();
        if (array_key_exists($countryCode, $countries)) {
            return $countries[$countryCode]["name"];
        }
        return $countryCode;
    }
    public function isValidCountryCode($countryCode)
    {
        return isset($this->countries[$countryCode]);
    }
    public function isValidCountryName($countryName)
    {
        return in_array($countryName, $this->getCountryNamesOnly());
    }
	

}
