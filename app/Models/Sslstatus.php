<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Sslstatus extends AbstractModel
{
	protected $table = 'sslstatus';
    protected $fillable = array("user_id", "domain_name");
    protected $booleans = array("active");
    protected $dates = array("start_date", "expiry_date", "last_synced_date");
    protected $allowAutoResync = true;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public static function factory($userId, $domainName)
    {
        $status = self::firstOrNew(["user_id" => $userId, "domain_name" => trim($domainName)]);
        return $status;
    }

    public function getImagePath()
    {
        if ($this->needsResync()) {
            return $this->getImageFilepath("ssl-loading.gif");
        }

        return $this->getImageFilepath("ssl-" . $this->getStatus() . ".png");
    }

    public function needsResync()
    {
        return $this->allowAutoResync && !($this->last_synced_date instanceof \Illuminate\Support\Carbon && $this->last_synced_date->diffInHours() < 24);
    }

    public function disableAutoResync()
    {
        $this->allowAutoResync = false;
        return $this;
    }

    protected function getImageFilepath($filename)
    {
        return asset("/assets/ssl/$filename");
    }

    public function getStatus()
    {
        if ($this->isActive()) {
            return "active";
        }
        return $this->isInactive() ? "inactive" : "unknown";
    }

    public function getStatusDisplayLabel()
    {
        if ($this->isActive()) {
            return __("admin.sslStatevalidSsl");
        }
        
        if ($this->isInactive()) {
            return __("admin.sslStatenoSsl");
        }

        return __("admin.sslStatesslUnknown");
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function isInactive()
    {
        if (!$this->exists) {
            return false;
        }

        return !$this->isActive();
    }

    public function getClass()
    {
        $classes = "ssl-state ssl-" . $this->getStatus();
        if ($this->needsResync()) {
            $classes .= " ssl-sync";
        }
        return $classes;
    }

    public function getTooltipContent()
    {
        $langStringKey = "admin.sslStatessl" . ucfirst($this->getStatus());
        if (auth()->guard("admin")->check()) {
            if ($this->needsResync()) {
                $langStringKey = "admin.loading";
            }

            return __($langStringKey, array("expiry" => $this->getFormattedExpiryDate()));
        }

        if ($this->needsResync()) {
            $langStringKey = "admin.loading";
        }

        return __($langStringKey, array("expiry" => $this->getFormattedExpiryDate()));
    }

    public function getFormattedExpiryDate()
    {
        $expiry = $this->expiry_date;
        if ($expiry instanceof \Illuminate\Support\Carbon) {
            return auth()->guard("admin")->check() 
                        ? \App\Helpers\Carbon::parse($expiry)->toAdminDateFormat() 
                        : \App\Helpers\Carbon::parse($expiry)->toClientDateFormat();
        }

        return "N/A";
    }

    protected function downloadAndSyncCertificate()
    {
        $certificate = (new \App\Helpers\Domain\Downloader())->getCertificate($this->domain_name);
        $this->subject_name = $certificate->getSubjectCommonName();
        $this->subject_org = $certificate->getSubjectOrg();
        $this->issuer_name = $certificate->getIssuerName();
        $this->issuer_name = $certificate->getIssuerOrg();
        $this->start_date = $certificate->getStartDate();
        $this->expiry_date = $certificate->getExpiryDate();
        $this->active = $certificate->getExpiryDate()->gte(\App\Helpers\Carbon::now());

        return $this;
    }

    public function syncAndSave()
    {
        try {
            $this->downloadAndSyncCertificate();
        } catch (\Exception $e) {
            $this->active = false;
        }

        $this->last_synced_date = \App\Helpers\Carbon::now();
        $this->save();

        return $this;
    }
}
