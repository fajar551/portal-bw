<?php

namespace App\Traits;

trait EmailPreferences
{
    public static $emailPreferencesDefaults = NULL;

    public function validateEmailPreferences(array $preferences)
    {
        if (!$preferences) {
            return NULL;
        }
        
        $preferenceKeys = array_filter(array_keys($preferences), function ($key) {
            return !in_array($key, \App\Helpers\Emailer::CLIENT_EMAILS ?? []);
        });

        if ($preferenceKeys && 0 < count($preferenceKeys)) {
            $preferenceKeys = implode(", ", $preferenceKeys);
            $valid = implode(", ", \App\Helpers\Emailer::CLIENT_EMAILS ?? []);

            throw new \InvalidArgumentException("Invalid Email Type: $preferenceKeys Valid options are: $valid");
        }

        if ($this instanceof \App\Models\Client) {
            if (\App\Helpers\Cfg::get("DisableClientEmailPreferences")) {
                return NULL;
            }

            if (isset($preferences[\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN]) && 
                !$preferences[\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN] && 
                $this->getEmailPreference(\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN) && 
                $this->contacts()->where(\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN . "emails", 1)->count() === 0
            ) {
                throw new \App\Exceptions\Validation\Required(__("admin.emailPreferencesdomainClientRequired"));
            }

        } else {
            if ($this instanceof \WHMCS\User\Client\Contact && 
                isset($preferences[\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN]) && 
                !$preferences[\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN] && 
                $this->getEmailPreference(\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN) && 
                !$this->client->getEmailPreference(\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN) && 
                $this->client->contacts()->where(\App\Helpers\Emailer::EMAIL_TYPE_DOMAIN . "emails", 1)->where("id", "!=", $this->id)->count() === 0
            ) {
                throw new \App\Exceptions\Validation\Required(__("admin.emailPreferencesdomainContactRequired"));
            }
        }

        return true;
    }

    public function getEmailPreferences()
    {
        if ($this instanceof \App\Models\Client) {
            if (!$this->emailPreferences) {
                return self::$emailPreferencesDefaults;
            }
            
            return $this->emailPreferences;
        }

        return [
            \App\Helpers\Emailer::EMAIL_TYPE_GENERAL => $this->receivesGeneralEmails, 
            \App\Helpers\Emailer::EMAIL_TYPE_INVOICE => $this->receivesInvoiceEmails, 
            \App\Helpers\Emailer::EMAIL_TYPE_SUPPORT => $this->receivesSupportEmails, 
            \App\Helpers\Emailer::EMAIL_TYPE_PRODUCT => $this->receivesProductEmails, 
            \App\Helpers\Emailer::EMAIL_TYPE_DOMAIN => $this->receivesDomainEmails, 
            \App\Helpers\Emailer::EMAIL_TYPE_AFFILIATE => $this->receivesAffiliateEmails
        ];
    }

    public function getEmailPreferencesDefault()
    {
        return [
            \App\Helpers\Emailer::EMAIL_TYPE_GENERAL => 0, 
            \App\Helpers\Emailer::EMAIL_TYPE_INVOICE => 0, 
            \App\Helpers\Emailer::EMAIL_TYPE_SUPPORT => 0, 
            \App\Helpers\Emailer::EMAIL_TYPE_PRODUCT => 0, 
            \App\Helpers\Emailer::EMAIL_TYPE_DOMAIN => 0, 
            \App\Helpers\Emailer::EMAIL_TYPE_AFFILIATE => 0,
        ];
    }

    public function getEmailPreference($type)
    {
        return $this->getEmailPreferences()[$type];
    }

    public function setEmailPreferences($preferences)
    {
        if (!count($preferences)) {
            return NULL;
        }

        $this->validateEmailPreferences($preferences);
        $storedPreferences = $this->getEmailPreferences();
        
        if ($this instanceof \App\Models\Client) {
            if (\App\Helpers\Cfg::get("DisableClientEmailPreferences")) {
                $preferences = self::$emailPreferencesDefaults;
            }

            $this->emailPreferences = array_merge($storedPreferences, $preferences);
        } else {
            if ($this instanceof \App\Models\Contact) {
                foreach ($preferences as $preference => $value) {
                    $var = $preference . "emails";
                    $this->{$var} = $value;
                }
            }
        }
    }
}
?>