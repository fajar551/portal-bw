<?php
namespace App\Helpers;

use DB;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Properties
{
	protected $model = NULL;
    protected static $fieldsToCustomFieldName = array("username" => "Username", "password" => "Password", "domain" => "Domain", "license" => "License Key", "dedicatedip" => "Dedicated IP", "diskusage" => "Disk Usage", "disklimit" => "Disk Limit", "bwusage" => "Bandwidth Usage", "bwlimit" => "Bandwidth Limit", "lastupdate" => "Last Update", "subscriptionid" => "Subscription ID");
    public function __construct($model)
    {
        $this->model = $model;
        return $this;
    }
	protected function isNativeField($fieldName)
    {
        return array_key_exists(strtolower($fieldName), self::$fieldsToCustomFieldName);
    }
    protected function getNativeFieldDisplayName($fieldName)
    {
        return $this->isNativeField($fieldName) ? self::$fieldsToCustomFieldName[strtolower($fieldName)] : $fieldName;
    }
    protected function getCustomField($model, $fieldName)
    {
        $baseQuery = \App\Models\Customfield::where("type", $model->isAddon() ? "addon" : "product")->where("relid", $model->isAddon() ? $model->addonId : $model->packageId);
        $queryClone = clone $baseQuery;
        $customField = $queryClone->where("fieldname", $fieldName)->first();
        if (is_null($customField)) {
            $customField = $baseQuery->where("fieldname", "like", $fieldName . "|%")->first();
        }
        return $customField;
    }
	protected function createCustomField($model, $fieldName, $fieldType)
    {
        $customField = new \App\Models\Customfield();
        $customField->type = $model->isAddon() ? "addon" : "product";
        $customField->fieldName = $fieldName;
        $customField->fieldType = $fieldType;
        $customField->relid = $model->isAddon() ? $model->addonId : $model->packageId;
        $customField->adminOnly = "on";
        $customField->save();
        return $customField;
    }
	public function save(array $data)
    {
        $updateData = array();
        foreach ($data as $fieldName => $value) {
            if ($this->model->isService() && $this->isNativeField($fieldName)) {
                $fieldName = strtolower($fieldName);
                if ($fieldName == "license") {
                    $fieldName = "domain";
                }
                $updateData[$fieldName] = $value;
            } else {
                $fieldName = $this->getNativeFieldDisplayName($fieldName);
                $fieldType = "text";
                if (is_array($value)) {
                    $fieldType = $value["type"];
                    $value = $value["value"];
                }
                $customField = $this->getCustomField($this->model, $fieldName);
                if (is_null($customField)) {
                    $customField = $this->createCustomField($this->model, $fieldName, $fieldType);
                }
                \App\Helpers\Functions::saveSingleCustomField($customField->id, $this->model->id, $value);
            }
        }
        if ($this->model->isService() && 0 < count($updateData)) {
            if (array_key_exists("password", $updateData)) {
                $updateData["password"] = (new \App\Helpers\Pwd())->encrypt($updateData["password"]);
            }
            DB::table("tblhosting")->where("id", "=", $this->model->id)->update($updateData);
        }
        return true;
    }
	public function get($fieldName)
    {
        if ($this->model->isService() && $this->isNativeField($fieldName)) {
            $fieldName = strtolower($fieldName);
            if ($fieldName == "license") {
                $fieldName = "domain";
            }
			return \App\Models\Hosting::select($fieldName)->where("id", $this->model->id)->value($fieldName);
        }
        $fieldName = $this->getNativeFieldDisplayName($fieldName);
        $customField = $this->getCustomField($this->model, $fieldName);
        if (is_null($customField)) {
            return "";
        }
        $customFieldValue = \App\Models\Customfieldsvalue::firstOrNew(array("fieldid" => $customField->id, "relid" => $this->model->id));
        return $customFieldValue ? $customFieldValue->value : "";
    }
}
