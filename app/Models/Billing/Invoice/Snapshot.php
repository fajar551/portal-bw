<?php

namespace App\Models\Billing\Invoice;

use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    //
    protected $table = "mod_invoicedata";
    public $timestamps = false;
    protected $primaryKey = "invoiceid";
    public $unique = array("invoiceid");
    protected $columnMap = array("invoiceId" => "invoiceid", "clientsDetails" => "clientsdetails", "customFields" => "customfields");
    protected $fillable = array("invoiceid", "clientsdetails", "customfields");
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, "invoiceid");
    }
    public function getClientsDetailsAttribute()
    {
        $rawClientsDetails = $this->attributes["clientsdetails"];
        $clientsDetails = json_decode($rawClientsDetails, true);
        if (!is_null($clientsDetails) && json_last_error() === JSON_ERROR_NONE) {
            return $clientsDetails;
        }
        return (new \App\Helpers\Client())->safe_unserialize($rawClientsDetails);
    }
    public function getCustomFieldsAttribute()
    {
        $rawCustomFields = $this->attributes["customfields"];
        $customFields = json_decode($rawCustomFields, true);
        if (!is_null($customFields) && json_last_error() === JSON_ERROR_NONE) {
            return $customFields;
        }
        return (new \App\Helpers\Client())->safe_unserialize($rawCustomFields);
    }
    public function setClientsDetailsAttribute(array $clientsDetails)
    {
        $this->attributes["clientsDetails"] = json_encode($clientsDetails);
    }
    public function setCustomFieldsAttribute(array $customFields)
    {
        $this->attributes["customFields"] = json_encode($customFields);
    }
}
