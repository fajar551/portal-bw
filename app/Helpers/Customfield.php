<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Customfield as CustomfieldModel;
use App\Models\Customfieldsvalue;
use App\Models\Product;
use App\Models\Hosting;
use Illuminate\Support\Facades\DB;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Database;
use Illuminate\Support\Facades\Storage;

class Customfield
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;

	}

	/**
	 * SaveCustomFields
	 *
	 */
	public static function SaveCustomFields($relid, $customfields, $type = "", $isAdmin = false)
	{
		$productTable = (new Product)->getTableName();
		$customfieldTable = (new CustomfieldModel)->getTableName();
		$hostingTable = (new Hosting)->getTableName();

		if (is_array($customfields)) {
			foreach ($customfields as $id => $value) {
				if (is_null($value)) {
					$value = "";
				}
				if (!is_int($id) && !empty($id)) {
					$stmt = CustomfieldModel::query();
					$stmt->where("{$customfieldTable}.fieldname", $id);
					if ($type) {
						$stmt->where("{$customfieldTable}.type", $type);
					}
					if ($type == "product") {
						$stmt->join($productTable, "{$productTable}.id", "=", "{$customfieldTable}.relid");
						$stmt->join($hostingTable, "{$hostingTable}.packageid", "=", "{$productTable}.id")->where("{$hostingTable}.id", "=", $relid);
					}
					$fieldIds = $stmt->get(["{$customfieldTable}.id"]);
					if (count($fieldIds) != 1) {
						continue;
					}
					$id = $fieldIds[0]->id;
				}

				$csfld = CustomfieldModel::query();
				$csfld->select('id');
				$csfld->filter(['type' => $type]);
				if (!$isAdmin) {
					$csfld->where('adminonly', "");
				}
				$csfldresult = $csfld->first();
				if (!$csfldresult) {
					continue;
				}

				// check file exists and value is file
				$image = self::isCustomfieldImageExists($id);
				if ($image && empty($value)) {
					$value = $image;
				} else {
                    // upload image and double check
                    // FIXME: Not working on addon customfield
					// dd(is_file($value));
					if (request()->hasFile("customfield.$id")) {
						$value = self::uploadCustomfieldImage("customfield.$id");
					}
				}

				// run hook
				$fieldsavehooks = \App\Helpers\Hooks::run_hook("CustomFieldSave", array("fieldid" => $id, "relid" => $relid, "value" => $value));
				if (0 < count($fieldsavehooks)) {
					$fieldsavehookslast = array_pop($fieldsavehooks);
					if (array_key_exists("value", $fieldsavehookslast)) {
						$value = $fieldsavehookslast["value"];
					}
				}

				$customFieldValue = Customfieldsvalue::firstOrNew(["fieldid" => $id, "relid" => $relid]);
				$customFieldValue->value = $value;
				$customFieldValue->save();
			}
		}
	}

	public static function isCustomfieldImageExists($id)
	{
		$customfield = \App\Models\Customfield::where(['id' => $id, 'fieldtype' => 'image'])->first();
		if (!$customfield) {
			return "";
		}
		$customfieldvalue = \App\Models\Customfieldsvalue::where(['fieldid' => $customfield->id])->first();
		$value = $customfieldvalue ? $customfieldvalue->value : "";
		$exists = Storage::disk('uploads')->exists($value);
		if (!$exists) {
			return "";
		}
		return $value;
	}

	public static function uploadCustomfieldImage($name)
	{
		try {
			if (request()->hasFile($name)) {
				$attachment = request()->file($name);
				$fileNameToSave = Str::random(6)."_".$attachment->getClientOriginalName();
				$filename = $fileNameToSave;
				$filepath = "{$filename}";
				$upload = Storage::disk('uploads')->put($filepath, file_get_contents($attachment), 'public');
				return $filepath;
			} else {
				return "";
			}
		} catch (\Exception $e) {
			return "";
		}
	}

	/**
	 * GetCustomFields
	 *
	 */
	public static function getCustomFields($type, $relid, $relid2, $admin = "", $order = "", $ordervalues = "", $hidepw = "")
	{
		global $_LANG;
        $customfields = array();
        if (is_null($relid) || $relid == "") {
            $relid = 0;
        }
        if (is_null($relid2) || $relid2 == "") {
            $relid2 = 0;
        }
        static $customFieldCache = NULL;
        if (!$customFieldCache) {
            $customFieldCache = array();
        }
        if (isset($customFieldCache[$type][$relid])) {
            $customFieldsData = $customFieldCache[$type][$relid];
        } else {
            $customFieldsData = CustomfieldModel::where("type", $type)->where("relid", $relid)->get();
            $customFieldCache[$type][$relid] = $customFieldsData;
        }
        if (!$admin) {
            $customFieldsData = $customFieldsData->where("adminonly", "");
        }
        if ($order) {
            $customFieldsData = $customFieldsData->where("showorder", "on");
        }

		foreach ($customFieldsData->toArray() as $data) {
			$id = $data["id"];
			$fieldname = $admin ? $data["fieldname"] : $data["fieldname"];
			if (strpos($fieldname, "|")) {
				$fieldname = explode("|", $fieldname);
				$fieldname = trim($fieldname[1]);
			}
			$fieldtype = $data["fieldtype"];
			$description = $admin ? $data["description"] : $data["description"];
			$fieldoptions = $data["fieldoptions"];
			$required = $data["required"];
			$adminonly = $data["adminonly"];
			$customfieldval = is_array($ordervalues) && array_key_exists($id, $ordervalues) ? $ordervalues[$id] : "";
			$input = "";
			if ($relid2) {
				$customFieldValue = Customfieldsvalue::firstOrNew(["fieldid" => $id, "relid" => $relid2]);
				if ($customFieldValue->exists) {
					$customfieldval = $customFieldValue->value;
				}

				$fieldloadhooks = \App\Helpers\Hooks::run_hook("CustomFieldLoad", array("fieldid" => $id, "relid" => $relid2, "value" => $customfieldval));
				if (0 < count($fieldloadhooks)) {
					$fieldloadhookslast = array_pop($fieldloadhooks);
					if (array_key_exists("value", $fieldloadhookslast)) {
						$customfieldval = $fieldloadhookslast["value"];
					}
				}
			}

			$rawvalue = $customfieldval;
			$customfieldval = \App\Helpers\Sanitize::makeSafeForOutput($customfieldval);
			if ($required == "on") {
				$required = "*";
			}

			if ($fieldtype == "text" || $fieldtype == "password" && $admin) {
				$input = "<input type=\"text\" name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"30\" class=\"form-control\" />";
			} else {
				if ($fieldtype == "link") {
						$webaddr = trim($customfieldval);
						if (substr($webaddr, 0, 4) == "www.") {
								$webaddr = "http://" . $webaddr;
						}
						$input = "<input type=\"text\" name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"40\" class=\"form-control\" /> " . ($customfieldval ? "<a href=\"" . $webaddr . "\" target=\"_blank\">www</a>" : "");
						$customfieldval = "<a href=\"" . $webaddr . "\" target=\"_blank\">" . $customfieldval . "</a>";
				} else {
						if ($fieldtype == "password") {
								$input = "<input type=\"password\" name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"30\" class=\"form-control\" />";
								if ($hidepw) {
										$pwlen = strlen($customfieldval);
										$customfieldval = "";
										for ($i = 1; $i <= $pwlen; $i++) {
												$customfieldval .= "*";
										}
								}
						} else {
								if ($fieldtype == "textarea") {
										$input = "<textarea name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" rows=\"3\" class=\"form-control\">" . $customfieldval . "</textarea>";
								} else {
										if ($fieldtype == "dropdown") {
												$input = "<select name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" class=\"form-control\">";
												if (!$required) {
														$input .= "<option value=\"\">" . $_LANG["none"] . "</option>";
												}
												$fieldoptions = explode(",", $fieldoptions);
												foreach ($fieldoptions as $optionvalue) {
														$input .= "<option value=\"" . $optionvalue . "\"";
														if ($customfieldval == $optionvalue) {
																$input .= " selected";
														}
														if (strpos($optionvalue, "|")) {
																$optionvalue = explode("|", $optionvalue);
																$optionvalue = trim($optionvalue[1]);
														}
														$input .= ">" . $optionvalue . "</option>";
												}
												$input .= "</select>";
										} else {
												if ($fieldtype == "tickbox") {
														$input = "<input type=\"checkbox\" name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\"";
														if ($customfieldval == "on") {
																$input .= " checked";
														}
														$input .= " />";
												} else {
													if ($fieldtype == "image") {
														$input = "<input type=\"file\" class=\"form-control-file\" name=\"customfield[" . $id . "]\" id=\"customfield".$id."\" accept=\"image/*\">";
														if ($customfieldval) {
															$image_url = Storage::disk('uploads')->url($customfieldval);
															// $input .= "<div class=\"row\"><div class=\"col-md-2 my-3\">";
															$input .= "<img src=\"".$image_url."\" class=\"img-fluid my-3\" alt=\"".$customfieldval."\" style=\"max-width: 15%\" />";
															// $input .= "</div></div>";
														}
													} else {
														if ($fieldtype == "hidden") {
															$input = "<input type=\"hidden\" name=\"customfield[" . $id . "]\" id=\"customfield" . $id . "\" value=\"" . $customfieldval . "\" size=\"30\" class=\"form-control\" />";
															if ($customfieldval) {
																$input .= "<p>$customfieldval</p>";
															}
														}
													}
												}
										}
								}
						}
				}
			}

			if ($fieldtype != "link" && strpos($customfieldval, "|")) {
				$customfieldval = explode("|", $customfieldval);
				$customfieldval = trim($customfieldval[1]);
			}
			$customfields[] = [
				"id" => $id,
				"textid" => preg_replace("/[^0-9a-z]/i", "", strtolower($fieldname)),
				"name" => $fieldname,
				"description" => $description,
				"type" => $fieldtype,
				"input" => $input,
				"value" => $customfieldval,
				"rawvalue" => $rawvalue,
				"required" => $required,
				"adminonly" => $adminonly
			];
		}

		return $customfields;
	}

	/**
	 * migrateCustomFieldsBetweenProducts
	 */
	public static function migrateCustomFieldsBetweenProducts($serviceid, $newpid, $save = false)
	{
		$existingPid = \App\Models\Hosting::find($serviceid);
		$existingPid = $existingPid ? $existingPid->packageid : 0;
		self::migrateCustomFieldsBetweenProductsOrAddons($serviceid, $newpid, $existingPid, $save);
	}

	/**
	 * migrateCustomFieldsBetweenProductsOrAddons
	 */
	public static function migrateCustomFieldsBetweenProductsOrAddons($entityId, $relatedItemId, $existingRelatedItemId, $save = false, $addon = false)
	{
		$type = $addon ? "addon" : "product";
		if ($save) {
			$customFieldsArray = array();
			$customFields = self::getcustomfields($type, $existingRelatedItemId, $entityId, true);
			foreach ($customFields as $v) {
				$k = $v["id"];
				if (request()->has('customfield')) {
                    // if (isset(Request::get("customfield")[$k])) {
					// }
					$customFieldsArray[$k] = request()->customfield[$k] ?? null;
				}
			}
			self::SaveCustomFields($entityId, $customFieldsArray, $type, true);
		}
		if ($existingRelatedItemId != $relatedItemId) {
			self::migratecustomfields($type, $entityId, $relatedItemId);
		}
	}

	/**
	 * migrateCustomFields
	 */
	public static function migrateCustomFields($itemType, $itemID, $newRelID)
	{
		switch ($itemType) {
			case "product":
				$existingRelID = \App\Models\Hosting::find($itemID);
				$existingRelID = $existingRelID ? $existingRelID->packageid : 0;
				break;
			case "support":
				$existingRelID = \App\Models\Ticket::find($itemID);
				$existingRelID = $existingRelID ? $existingRelID->did : 0;
				break;
			case "addon":
				$existingRelID = \App\Models\Hostingaddon::find($itemID);
				$existingRelID = $existingRelID ? $existingRelID->addonid : 0;
				break;
			default:
				$existingRelID = 0;
		}
		if (!$existingRelID || $existingRelID == $newRelID) {
			return false;
		}
		$customfields = self::getCustomFields($itemType, $existingRelID, $itemID, true);
		$dataArr = array();
		$marketConnectOrderNumberValue = NULL;
		foreach ($customfields as $v) {
			$cfid = $v["id"];
			$cfname = $v["name"];
			$cfval = $v["rawvalue"];
			$dataArr[$cfname] = $cfval;
			\App\Models\Customfieldsvalue::where('fieldid', $cfid)->where('relid', $itemID)->delete();
			if ($cfname == "Order Number" && $cfval) {
				$marketConnectOrderNumberValue = $cfval;
			}
		}
		$hasMarketConnectOrderNumberField = false;
		$customfields = self::getCustomFields($itemType, $newRelID, "", true);
		$newProductCustomFieldNames = array();
		foreach ($customfields as $v) {
			$cfid = $v["id"];
			$cfname = $v["name"];
			$newProductCustomFieldNames[] = $cfname;
			if (isset($dataArr[$cfname])) {
				\App\Models\Customfieldsvalue::insert(array("fieldid" => $cfid, "relid" => $itemID, "value" => $dataArr[$cfname]));
			}
		}
		if (!is_null($marketConnectOrderNumberValue) && !in_array("Order Number", $newProductCustomFieldNames)) {
			$orderNumberFieldId = \App\Models\Customfield::insert(array("type" => $itemType, "relid" => $newRelID, "fieldname" => "Order Number", "fieldtype" => "text", "adminonly" => 1));
			\App\Models\Customfieldsvalue::insert(array("fieldid" => $orderNumberFieldId, "relid" => $itemID, "value" => $marketConnectOrderNumberValue));
		}
	}

    public static function copyCustomFieldValues($itemType, $fromItemId, $toItemId){
        $prefix=Database::prefix();
        if ($fromItemId === $toItemId) {
            return false;
        }
        switch ($itemType) {
            case "product":
                $sourceFieldRelId = DB::table($prefix."hosting")->where("id", "=", $fromItemId)->value("packageid");
                $destFieldRelId = DB::table($prefix."hosting")->where("id", "=", $toItemId)->value("packageid");
                break;
            case "support":
                $sourceFieldRelId =DB::table($prefix."tickets")->where("id", "=", $fromItemId)->value("did");
                $destFieldRelId =DB::table($prefix."tickets")->where("id", "=", $toItemId)->value("did");
                break;
            default:
                return false;
        }
        if (!$sourceFieldRelId || !$destFieldRelId) {
            return false;
        }
        $sourceCustomFields = array();
        foreach (getcustomfields($itemType, $sourceFieldRelId, $fromItemId, true) as $field) {
            $sourceCustomFields[$field["name"]] = $field;
        }
        $destCustomFields = array();
        foreach (getcustomfields($itemType, $destFieldRelId, "", true) as $field) {
            $destCustomFields[$field["name"]] = $field;
        }
        foreach ($destCustomFields as $destFieldName => $destFieldData) {
            if (isset($sourceCustomFields[$destFieldName])) {
               DB::table($prefix."customfieldsvalues")->updateOrInsert(array("fieldid" => $destFieldData["id"], "relid" => $toItemId), array("value" => $sourceCustomFields[$destFieldName]["rawvalue"]));
            }
        }
    }



}
