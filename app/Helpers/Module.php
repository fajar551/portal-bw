<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Module
{
    /**
     * moduleConfigFieldOutput
     */
	public static function moduleConfigFieldOutput($values)
    {
        if (is_null($values["Value"])) {
            $values["Value"] = isset($values["Default"]) ? $values["Default"] : "";
        }
        if (empty($values["Size"])) {
            $values["Size"] = 40;
        }
        $inputClass = "input-";
        switch (true) {
            case $values["Size"] <= 10:
                $inputClass .= "100";
                break;
            case $values["Size"] <= 20:
                $inputClass .= "200";
                break;
            case $values["Size"] <= 30:
                $inputClass .= "300";
                break;
            default:
                $inputClass .= "400";
                break;
        }
        switch ($values["Type"]) {
            case "text":
                $code = "<input type=\"text\" name=\"" . $values["Name"] . "\" class=\"form-control d-inline " . $inputClass . "\" value=\"" . \App\Helpers\Sanitize::encode($values["Value"]) . "\"" . (isset($values["Placeholder"]) ? " placeholder=\"" . $values["Placeholder"] . "\"" : "") . (!empty($values["Disabled"]) ? " disabled" : "") . (!empty($values["ReadOnly"]) ? " readonly=\"readonly\"" : "") . " />";
                if (isset($values["Description"])) {
                    $code .= " " . $values["Description"];
                }
                break;
            case "password":
                $code = "<input type=\"password\" autocomplete=\"off\" name=\"" . $values["Name"] . "\" class=\"form-control d-inline " . $inputClass . "\" value=\"" . \App\Helpers\Adminfunctions::replacePasswordWithMasks($values["Value"]) . "\"" . (!empty($values["ReadOnly"]) ? " readonly=\"readonly\"" : "") . " />";
                if (isset($values["Description"])) {
                    $code .= " " . $values["Description"];
                }
                break;
            case "yesno":
                $code = "<label class=\"checkbox-inline\"><input type=\"hidden\" name=\"" . $values["Name"] . "\" value=\"\">" . "<input type=\"checkbox\" name=\"" . $values["Name"] . "\"";
                if (!empty($values["Value"])) {
                    $code .= " checked=\"checked\"";
                }
                $code .= " /> " . (isset($values["Description"]) ? $values["Description"] : "&nbsp") . "</label>";
                break;
            case "dropdown":
                $code = "<select name=\"" . $values["Name"];
                if (isset($values["Multiple"])) {
                    $size = isset($values["Size"]) && is_numeric($values["Size"]) ? $values["Size"] : 3;
                    $code .= "[]\" multiple=\"true\" size=\"" . $size . "\"";
                    $selectedKeys = json_decode($values["Value"]);
                } else {
                    $code .= "\"";
                    $selectedKeys = array($values["Value"]);
                }
                $code .= " class=\"form-control select-inline\"" . (!empty($values["ReadOnly"]) ? " readonly=\"readonly\"" : "") . ">";
                $dropdownOptions = $values["Options"];
                if (is_array($dropdownOptions)) {
                    foreach ($dropdownOptions as $key => $value) {
                        $code .= "<option value=\"" . $key . "\"";
                        if (in_array($key, $selectedKeys)) {
                            $code .= " selected=\"selected\"";
                        }
                        $code .= ">" . $value . "</option>";
                    }
                } else {
                    $dropdownOptions = explode(",", $dropdownOptions);
                    foreach ($dropdownOptions as $value) {
                        $code .= "<option value=\"" . $value . "\"";
                        if (in_array($value, $selectedKeys)) {
                            $code .= " selected=\"selected\"";
                        }
                        $code .= ">" . $value . "</option>";
                    }
                }
                $code .= "</select>";
                if (isset($values["Description"])) {
                    $code .= " " . $values["Description"];
                }
                break;
            case "radio":
                $code = "";
                if (isset($values["Description"])) {
                    $code .= $values["Description"] . "<br />";
                }
                $options = $values["Options"];
                if (!is_array($options)) {
                    $options = explode(",", $options);
                }
                if (!isset($values["Value"])) {
                    $values["Value"] = $options[0];
                }
                foreach ($options as $value) {
                    $code .= "<label class=\"radio-inline\"><input type=\"radio\" name=\"" . $values["Name"] . "\" value=\"" . $value . "\"";
                    if ($values["Value"] == $value) {
                        $code .= " checked=\"checked\"";
                    }
                    $code .= " /> " . $value . "</label><br />";
                }
                break;
            case "textarea":
                $cols = isset($values["Cols"]) ? $values["Cols"] : "60";
                $rows = isset($values["Rows"]) ? $values["Rows"] : "5";
                $code = "<textarea class=\"form-control\" name=\"" . $values["Name"] . "\" cols=\"" . $cols . "\" rows=\"" . $rows . "\"" . (!empty($values["ReadOnly"]) ? " readonly=\"readonly\"" : "") . ">" . \App\Helpers\Sanitize::encode($values["Value"]) . "</textarea>";
                if (isset($values["Description"])) {
                    $code .= $values["Description"];
                }
                break;
            default:
                $code = $values["Description"];
        }
        return $code;
    }
}
