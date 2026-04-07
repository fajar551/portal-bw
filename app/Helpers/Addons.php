<?php

namespace App\Helpers;

use App\Models\Hostingaddon;
use App\Models\Hosting;
use App\Events\ClientAddonActivated;
use App\Events\ClientAddonSuspended;
use App\Events\ClientAddonTerminated;
use App\Events\ClientAddonCancelled;
use App\Events\ClientAddonFraud;
use App\Events\ClientAddonEdit;


// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Addons
{
	//protected $request;

	public function __construct()
	{
		//$this->request = $request;
	}

	// Write static function here

	/***
	 * UpdateClientAddon
	 * 
	 * @param id int The ID of the client addon to update Required
	 * @param status The status to change the addon to. Optional
	 * @param termination Date The termination date of the addon. Format: Y-m-d Optional
	 * @param addonid int The configured addon ID to update the client addon to. Optional
	 * @param name string The custom name to apply to the addon. Optional
	 * @param setupfee float  The setup fee for the client addon. Optional
	 * @param recurring float  The recurring amount for the client addon. Optional
	 * @param billingcycle string  The billing cycle for the addon. Optional
	 * @param nextduedate string  The next due date for the addon. Format: Y-m-d Optional
	 * @param terminationDate string  The termination date of the addon. Format: Y-m-d Optional
	 * @param notes string  The admin notes to associate with the addon. Optional
	 * @param autorecalc bool  Whether to automatically recalculate the recurring amount of the addon (this will ignore any passed $recurring). Optional



	 */


	public function UpdateClientAddon($params = [])
	{
		//extract($params);
		$id = (int) $params['id'];
		$data = Hostingaddon::find($id)->toArray();
		$params['nextinvoicedate']=$params['nextduedate'];
		extract($params);
		if (!$data['id']) {
			return ["result" => "error", "message" => "Addon ID Not Found"];
		}
		$serviceid = $data['hostingid'];
		$currentstatus = $data['status'];
		$userid = (int) Hosting::find($serviceid)->userid;
		$status = $params['status'];
	 	$terminationDate  = $params['terminationDate'];
		$updateqry = array();
		if ($addonid) {
			$updateqry["addonid"] = $addonid;
		} else {
			$addonid = $data["addonid"];
		}
		if ($name) {
			$updateqry["name"] = $name;
		}
		if ($setupfee) {
			$updateqry["setupfee"] = $setupfee;
		}
		if ($recurring) {
			$updateqry["recurring"] = $recurring;
		}
		if ($billingcycle) {
			$updateqry["billingcycle"] = $billingcycle;
		}
		if ($nextduedate) {
			$updateqry["nextduedate"] = $nextduedate;
		}
		if ($nextinvoicedate) {
			$updateqry["nextinvoicedate"] = $nextinvoicedate;
		}
		if ($notes) {
			$updateqry["notes"] = $notes;
		}
		if ($status && $status != $currentstatus) {
			switch ($status) {
				case "Terminated":
				case "Cancelled":
					if ((!$terminationDate || $terminationDate == "0000-00-00") && !in_array($currentstatus, array("Terminated", "Cancelled"))) {
						$terminationDate = date("Y-m-d");
					}
					break;
				default:
					$terminationDate = "0000-00-00";
			}
			$updateqry["status"] = $status;
		}
		if ($terminationDate) {
			if (!$status) {
				switch ($currentstatus) {
					case "Terminated":
					case "Cancelled":
						if ($terminationDate == "0000-00-00") {
							$terminationDate = date("Y-m-d");
						}
						break;
					default:
						$terminationDate = "0000-00-00";
				}
			}
			$updateqry["termination_date"] = $terminationDate;
		}
	
		if (0 < count($updateqry)) {
			//tblhostingaddons
			$upate=Hostingaddon::find($id);
			$upate->addonid =$updateqry['addonid'];
			$upate->name =$updateqry['name'];
			$upate->recurring =$updateqry['recurring'];
			$upate->billingcycle =$updateqry['billingcycle'];
			$upate->nextduedate =$updateqry['nextduedate'];
			$upate->nextinvoicedate =$updateqry['nextinvoicedate'];
			$upate->notes =$updateqry['notes'];
			$upate->termination_date =$updateqry['termination_date'];
			if ($status && $status != $currentstatus) {
				$upate->status =$updateqry['status'];
			}
			$upate->save();
			
			$hsParams=array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid);
			if ($currentstatus != "Active" && $status == "Active") {
				\App\Helpers\Hooks::run_hook("ClientAddonActivated",$hsParams);
			}else{
				if ($currentstatus != "Suspended" && $status == "Suspended") {
					\App\Helpers\Hooks::run_hook("ClientAddonSuspended",$hsParams);
				} else {
					if ($currentstatus != "Terminated" && $status == "Terminated") {
						\App\Helpers\Hooks::run_hook("ClientAddonTerminated",$hsParams);
					} else {
						if ($currentstatus != "Cancelled" && $status == "Cancelled") {
							\App\Helpers\Hooks::run_hook("ClientAddonCancelled",$hsParams);
						} else {
							if ($currentstatus != "Fraud" && $status == "Fraud") {
								\App\Helpers\Hooks::run_hook("ClientAddonFraud",$hsParams);
							} else {
								\App\Helpers\Hooks::run_hook("ClientAddonFraud",$hsParams);
							}
						}
					}
				}
			}
			$result = array("result" => "success", "id" => $id);
		}else{
			$result = array("result" => "error", "id" => $id, "message" => "Nothing to Update");
		}

		return $result;
	}
}
