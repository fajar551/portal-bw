<?php

namespace App\Helpers;

use Auth;

// Import Model Class here

// Import Package Class here
use App\Helpers\Hooks;
use App\Helpers\Cfg;
use App\Helpers\LogActivity;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\Format;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ClientHelper
{
	public function convertStateToCode($ostate, $country)
	{
		$sc = "";
		$state = strtolower($ostate);
		$country = strtoupper($country);
		if ($country == "US") {
			if ($state == "alabama") {
				$sc = "AL";
			} else {
				if ($state == "alaska") {
					$sc = "AK";
				} else {
					if ($state == "arizona") {
						$sc = "AZ";
					} else {
						if ($state == "arkansas") {
							$sc = "AR";
						} else {
							if ($state == "california") {
								$sc = "CA";
							} else {
								if ($state == "colorado") {
									$sc = "CO";
								} else {
									if ($state == "connecticut") {
										$sc = "CT";
									} else {
										if ($state == "delaware") {
											$sc = "DE";
										} else {
											if ($state == "florida") {
												$sc = "FL";
											} else {
												if ($state == "georgia") {
													$sc = "GA";
												} else {
													if ($state == "hawaii") {
														$sc = "HI";
													} else {
														if ($state == "idaho") {
															$sc = "ID";
														} else {
															if ($state == "illinois") {
																$sc = "IL";
															} else {
																if ($state == "indiana") {
																	$sc = "IN";
																} else {
																	if ($state == "iowa") {
																		$sc = "IA";
																	} else {
																		if ($state == "kansas") {
																			$sc = "KS";
																		} else {
																			if ($state == "kentucky") {
																				$sc = "KY";
																			} else {
																				if ($state == "louisiana") {
																					$sc = "LA";
																				} else {
																					if ($state == "maine") {
																						$sc = "ME";
																					} else {
																						if ($state == "maryland") {
																							$sc = "MD";
																						} else {
																							if ($state == "massachusetts") {
																								$sc = "MA";
																							} else {
																								if ($state == "michigan") {
																									$sc = "MI";
																								} else {
																									if ($state == "minnesota") {
																										$sc = "MN";
																									} else {
																										if ($state == "mississippi") {
																											$sc = "MS";
																										} else {
																											if ($state == "missouri") {
																												$sc = "MO";
																											} else {
																												if ($state == "montana") {
																													$sc = "MT";
																												} else {
																													if ($state == "nebraska") {
																														$sc = "NE";
																													} else {
																														if ($state == "nevada") {
																															$sc = "NV";
																														} else {
																															if ($state == "new hampshire") {
																																$sc = "NH";
																															} else {
																																if ($state == "new jersey") {
																																	$sc = "NJ";
																																} else {
																																	if ($state == "new mexico") {
																																		$sc = "NM";
																																	} else {
																																		if ($state == "new york") {
																																			$sc = "NY";
																																		} else {
																																			if ($state == "north carolina") {
																																				$sc = "NC";
																																			} else {
																																				if ($state == "north dakota") {
																																					$sc = "ND";
																																				} else {
																																					if ($state == "ohio") {
																																						$sc = "OH";
																																					} else {
																																						if ($state == "oklahoma") {
																																							$sc = "OK";
																																						} else {
																																							if ($state == "oregon") {
																																								$sc = "OR";
																																							} else {
																																								if ($state == "pennsylvania") {
																																									$sc = "PA";
																																								} else {
																																									if ($state == "rhode island") {
																																										$sc = "RI";
																																									} else {
																																										if ($state == "south carolina") {
																																											$sc = "SC";
																																										} else {
																																											if ($state == "south dakota") {
																																												$sc = "SD";
																																											} else {
																																												if ($state == "tennessee") {
																																													$sc = "TN";
																																												} else {
																																													if ($state == "texas") {
																																														$sc = "TX";
																																													} else {
																																														if ($state == "utah") {
																																															$sc = "UT";
																																														} else {
																																															if ($state == "vermont") {
																																																$sc = "VT";
																																															} else {
																																																if ($state == "virginia") {
																																																	$sc = "VA";
																																																} else {
																																																	if ($state == "washington") {
																																																		$sc = "WA";
																																																	} else {
																																																		if ($state == "west virginia") {
																																																			$sc = "WV";
																																																		} else {
																																																			if ($state == "wisconsin") {
																																																				$sc = "WI";
																																																			} else {
																																																				if ($state == "wyoming") {
																																																					$sc = "WY";
																																																				}
																																																			}
																																																		}
																																																	}
																																																}
																																															}
																																														}
																																													}
																																												}
																																											}
																																										}
																																									}
																																								}
																																							}
																																						}
																																					}
																																				}
																																			}
																																		}
																																	}
																																}
																															}
																														}
																													}
																												}
																											}
																										}
																									}
																								}
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		} else {
			if ($country == "CA") {
				if ($state == "alberta") {
					$sc = "AB";
				} else {
					if ($state == "british columbia") {
						$sc = "BC";
					} else {
						if ($state == "manitoba") {
							$sc = "MB";
						} else {
							if ($state == "new brunswick") {
								$sc = "NB";
							} else {
								if ($state == "newfoundland") {
									$sc = "NL";
								} else {
									if ($state == "northwest territories") {
										$sc = "NT";
									} else {
										if ($state == "nova scotia") {
											$sc = "NS";
										} else {
											if ($state == "nunavut") {
												$sc = "NU";
											} else {
												if ($state == "ontario") {
													$sc = "ON";
												} else {
													if ($state == "prince edward island") {
														$sc = "PE";
													} else {
														if ($state == "quebec") {
															$sc = "QC";
														} else {
															if ($state == "saskatchewan") {
																$sc = "SK";
															} else {
																if ($state == "yukon") {
																	$sc = "YT";
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if (!$sc) {
			$sc = $ostate;
		}
		return $sc;
	}

	public static function formatPhoneNumber($details)
	{
		$phone = trim($details["phonenumber"]);
		$phonePrefix = "";
		if (substr($phone, 0, 1) == "+") {
			$phoneParts = explode(".", ltrim($phone, "+"), 2);
			if (count($phoneParts) == 2) {
				list($phonePrefix, $phoneNumber) = $phoneParts;
			} else {
				$phoneNumber = $phoneParts[0];
			}
		} else {
			$phoneNumber = $phone;
		}
		$phonePrefix = preg_replace("/[^0-9]/", "", $phonePrefix);
		$phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);
		$countries = new \App\Helpers\Country();
		if (!$phonePrefix) {
			$phonePrefix = $countries->getCallingCode($details["countrycode"]);
		}
		$trimmedPhoneNumber = $phoneNumber;
		if ($phonePrefix != $countries->getCallingCode("IT")) {
			$trimmedPhoneNumber = ltrim($trimmedPhoneNumber, "0");
		}
		$fullyFormattedPhoneNumber = $phonePrefix ? "+" . $phonePrefix . "." . $trimmedPhoneNumber : $phoneNumber;
		$details["phonenumber"] = $phoneNumber;
		$details["phonecc"] = $phonePrefix;
		$details["phonenumberformatted"] = $phoneNumber ? $fullyFormattedPhoneNumber : $phoneNumber;
		$details["telephoneNumber"] = \App\Helpers\Cfg::get("PhoneNumberDropdown") ? $details["phonenumberformatted"] : $phone;
		//dd($details);
		return $details;
	}

	public function getClientDefaultCardDetails($userId, $mode = "allowLegacy", $paymentModule = NULL)
	{
		$cardDetails = array("cardtype" => NULL, "cardlastfour" => NULL, "cardnum" => 'No existing card details on record', "fullcardnum" => NULL, "expdate" => "", "startdate" => "", "issuenumber" => NULL, "gatewayid" => NULL);
		try {
			$client = \App\Models\Client::findOrFail($userId);
			//dd($client->payMethods->creditCards()); die();
			if (!in_array($mode, array("forceLegacy", "forcePayMethod", "allowLegacy"))) {
				$mode = "allowLegacy";
			}
			if ($mode == "forceLegacy") {
				return $this->getCCDetails($userId);
			}
			if ($mode == "allowLegacy" && $client->needsCardDetailsMigrated()) {
				return $this->getCCDetails($userId);
			}
			/* $payMethods = $client->payMethods->creditCards();
			if ($paymentModule) {
				$payMethods = $payMethods->forGateway($paymentModule);
			} */
			//$gateway = new WHMCS\Module\Gateway();
			$payMethod = NULL;
			/* foreach ($payMethods as $tryPayMethod) {
				if (!$tryPayMethod->isUsingInactiveGateway()) {
					$payMethod = $tryPayMethod;
					break;
				}
			} */
			//$cardDetails = getPayMethodCardDetails($payMethod);
			if ($payMethod) {
				$cardDetails["payMethod"] = $payMethod;
			}
		} catch (Exception $e) {
		}
		return $cardDetails;
	}

	public function getCCDetails($userid)
	{
		//$config = DI::make("config");
		$cc_encryption_hash = $this->applicationConfig();
		$cchash = md5($cc_encryption_hash . $userid);
		$prefix = \App\Helpers\Database::prefix();

		//$result = select_query("tblclients", "cardtype,cardlastfour,AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum,AES_DECRYPT(expdate,'" . $cchash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cchash . "') as startdate,gatewayid,billingcid", array("id" => $userid));
		//$data = mysql_fetch_array($result);

		$data = DB::table($prefix . 'clients')->select(DB::raw("cardtype,cardlastfour,AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum,AES_DECRYPT(expdate,'" . $cchash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cchash . "') as startdate,gatewayid,billingcid"))->where('id', $userid)->first()->toArray();


		$carddata = array();
		$carddata["cardtype"] = $data["cardtype"];
		$carddata["cardlastfour"] = $data["cardlastfour"];
		$carddata["cardnum"] = $data["cardlastfour"] ? "************" . $data["cardlastfour"] : \Lang::get("nocarddetails");
		$carddata["fullcardnum"] = $data["cardnum"];
		$carddata["expdate"] = $data["expdate"] ? substr($data["expdate"], 0, 2) . "/" . substr($data["expdate"], 2, 2) : "";
		$carddata["startdate"] = $data["startdate"] ? substr($data["startdate"], 0, 2) . "/" . substr($data["startdate"], 2, 2) : "";
		$carddata["issuenumber"] = $data["issuenumber"];
		$carddata["gatewayid"] = $data["gatewayid"];
		$carddata["billingcontactid"] = $data["billingcid"];
		$carddata["payMethod"] = NULL;
		return $carddata;
	}

	public static function applicationConfig()
	{
		return config('portal.hash.cc_encryption_hash');
	}

	public function getClientsStats($userid, \App\Models\Client $client = NULL)
	{
		//global $currency;
		//$currency = getCurrency($userid);
		$getAdmin=new \App\Helpers\AdminFunctions();
		$currency =$getAdmin->getCurrency($userid);
		$stats = array();
		if (is_null($client) || $client->id != $userid) {
			$client = \App\Models\Client::find($userid);
		}

		$prefix=\Database::prefix();
		
		$invoiceTypeItemInvoiceIds = DB::table($prefix."invoiceitems")->where("userid", $userid)->where("type", "Invoice")->pluck("invoiceid");
		$invoiceAddFundsTypeItemInvoiceIds = DB::table($prefix."invoiceitems")->where("userid", $userid)->whereIn("type", array("AddFunds", "Invoice"))->pluck("invoiceid");
		$invoicesData = DB::table("tblinvoices")->where($prefix."invoices.userid", $userid)->where("status", "Unpaid")->leftJoin($prefix."accounts", "tblaccounts.invoiceid", "=", $prefix."invoices.id")->whereNotIn($prefix."invoices.id", $invoiceTypeItemInvoiceIds)->first(array(DB::raw("IFNULL(count({$prefix}invoices.id), 0) as invoice_count"), DB::raw("IFNULL(SUM(total), 0) as total"), DB::raw("IFNULL(SUM(amountin), 0) as amount_in"), DB::raw("IFNULL(SUM(amountout), 0) as amount_out")));
		$stats["numdueinvoices"] = $invoicesData->invoice_count;
		$stats["dueinvoicesbalance"] = Format::formatCurrency($invoicesData->total - $invoicesData->amount_in + $invoicesData->amount_out);
		$stats["incredit"] = $client ? 0 < $client->credit : false;
		$stats["creditbalance"] = Format::formatCurrency($client ? $client->credit : 0);
		$transactionsData = DB::table($prefix."accounts")->where("userid", $userid)->first(array(DB::raw("IFNULL(SUM(fees), 0) as fees"), DB::raw("IFNULL(SUM(amountin), 0) as amount_in"), DB::raw("IFNULL(SUM(amountout), 0) as amount_out")));
		$stats["grossRevenue"] = Format::formatCurrency($transactionsData->amount_in);
		$stats["expenses"] = Format::formatCurrency($transactionsData->fees + $transactionsData->amount_out);
		$stats["income"] = Format::formatCurrency($transactionsData->amount_in - $transactionsData->fees - $transactionsData->amount_out);
		$overDueInvoices = DB::table($prefix."invoices")->where($prefix."invoices.userid", $userid)->where("status", "Unpaid")->where("duedate", "<", Carbon::today()->toDateTimeString())->leftJoin($prefix."accounts", $prefix."accounts.invoiceid", "=", $prefix."invoices.id")->whereNotIn($prefix."invoices.id", $invoiceTypeItemInvoiceIds)->first(array(DB::raw("IFNULL(count(tblinvoices.id), 0) as invoice_count"), DB::raw("IFNULL(SUM(total), 0) as total"), DB::raw("IFNULL(SUM(amountin), 0) as amount_in"), DB::raw("IFNULL(SUM(amountout), 0) as amount_out")));
		$stats["numoverdueinvoices"] = $overDueInvoices->invoice_count;
		$stats["overdueinvoicesbalance"] = Format::formatCurrency($invoicesData->total - $invoicesData->amount_in + $invoicesData->amount_out);
		$invoicesData = DB::table($prefix."invoices")->where($prefix."invoices.userid", $userid)->where("status", "Draft")->leftJoin($prefix."accounts", $prefix."accounts.invoiceid", "=", $prefix."invoices.id")->whereNotIn($prefix."invoices.id", $invoiceTypeItemInvoiceIds)->first(array(DB::raw("IFNULL(count({$prefix}invoices.id), 0) as invoice_count"), DB::raw("IFNULL(SUM(total), 0) as total"), DB::raw("IFNULL(SUM(amountin), 0) as amount_in"), DB::raw("IFNULL(SUM(amountout), 0) as amount_out")));
		$stats["numDraftInvoices"] = $invoicesData->invoice_count;
		$stats["draftInvoicesBalance"] = Format::formatCurrency($invoicesData->total - $invoicesData->amount_in + $invoicesData->amount_out);
		$invoiceStatus = array("Unpaid" => array("invoice_count" => 0, "total" => 0, "credit" => 0), "Paid" => array("invoice_count" => 0, "total" => 0, "credit" => 0), "Cancelled" => array("invoice_count" => 0, "total" => 0, "credit" => 0), "Refunded" => array("invoice_count" => 0, "total" => 0, "credit" => 0), "Collections" => array("invoice_count" => 0, "total" => 0, "credit" => 0), "Payment Pending" => array("invoice_count" => 0, "total" => 0, "credit" => 0));
		$invoiceData = DB::table($prefix."invoices")->where("userid", $userid)->whereNotIn($prefix."invoices.id", $invoiceAddFundsTypeItemInvoiceIds)->groupBy("status")->get(array("status", DB::raw("count({$prefix}invoices.id) as invoice_count"), DB::raw("SUM(IFNULL(total, 0)) as total"), DB::raw("SUM(IFNULL(credit, 0)) as credit")));
		foreach ($invoiceData as $invoiceDatum) {
			$invoiceStatus[$invoiceDatum->status]["invoice_count"] = $invoiceDatum->invoice_count;
			$invoiceStatus[$invoiceDatum->status]["total"] = $invoiceDatum->total;
			$invoiceStatus[$invoiceDatum->status]["credit"] = $invoiceDatum->credit;
		}
		foreach ($invoiceStatus as $status => $invoiceCounts) {
			$statusKey = strtolower(str_replace(" ", "", $status));
			$key = "num" . $statusKey . "invoices";
			$stats[$key] = $invoiceCounts["invoice_count"];
			$key = $statusKey . "invoicesamount";
			$value = $invoiceCounts["total"];
			if ($status == "Paid") {
				$value += $invoiceCounts["credit"];
			}
			$stats[$key] = Format::formatCurrency($value);
		}
		$productstats = array();
		//$result = full_query("SELECT tblproducts.type,domainstatus,COUNT(*) FROM tblhosting INNER JOIN tblproducts ON tblhosting.packageid=tblproducts.id WHERE tblhosting.userid=" . (int) $userid . " GROUP BY domainstatus,tblproducts.type");
		$data=DB::table($prefix.'hosting')
				->join("{$prefix}products","{$prefix}hosting.packageid","=","{$prefix}products.id")
				->where("{$prefix}hosting.userid",(int)$userid)
				->selectRaw("{$prefix}products.type , domainstatus ,COUNT(*) as count")
				->groupBy('domainstatus')
				->groupBy("{$prefix}products.type")
				->get();

		foreach($data  as $r){
			$productstats[$r->type][$r->domainstatus] = $r->count;
		}

		$stats["productsnumactivehosting"] = isset($productstats["hostingaccount"]["Active"]) ? $productstats["hostingaccount"]["Active"] : 0;
		$stats["productsnumhosting"] = 0;
		if (array_key_exists("hostingaccount", $productstats) && is_array($productstats["hostingaccount"])) {
			foreach ($productstats["hostingaccount"] as $status => $count) {
				$stats["productsnumhosting"] += $count;
			}
		}
		$stats["productsnumactivereseller"] = isset($productstats["reselleraccount"]["Active"]) ? $productstats["reselleraccount"]["Active"] : 0;
		$stats["productsnumreseller"] = 0;
		if (array_key_exists("reselleraccount", $productstats) && is_array($productstats["reselleraccount"])) {
			foreach ($productstats["reselleraccount"] as $status => $count) {
				$stats["productsnumreseller"] += $count;
			}
		}
		$stats["productsnumactiveservers"] = isset($productstats["server"]["Active"]) ? $productstats["server"]["Active"] : 0;
		$stats["productsnumservers"] = 0;
		if (array_key_exists("server", $productstats) && is_array($productstats["server"])) {
			foreach ($productstats["server"] as $status => $count) {
				$stats["productsnumservers"] += $count;
			}
		}
		$stats["productsnumactiveother"] = isset($productstats["other"]["Active"]) ? $productstats["other"]["Active"] : 0;
		$stats["productsnumother"] = 0;
		if (array_key_exists("other", $productstats) && is_array($productstats["other"])) {
			foreach ($productstats["other"] as $status => $count) {
				$stats["productsnumother"] += $count;
			}
		}
		$stats["productsnumactive"] = $stats["productsnumactivehosting"] + $stats["productsnumactivereseller"] + $stats["productsnumactiveservers"] + $stats["productsnumactiveother"];
		$stats["productsnumtotal"] = $stats["productsnumhosting"] + $stats["productsnumreseller"] + $stats["productsnumservers"] + $stats["productsnumother"];
		//dd($stats);
		$domainstats = array();
		$result=\App\Models\Domain::where('userid',(int) $userid)->selectRaw("status,COUNT(*) as count")->groupBy('status')->get();
		foreach($result as $r){
			$domainstats[$r->status] = $r->count;
		}
		
		$stats["numactivedomains"] = isset($domainstats["Active"]) ? $domainstats["Active"] : 0;
		$stats["numdomains"] = 0;
		foreach ($domainstats as $count) {
			$stats["numdomains"] += $count;
		}
		$quotestats = array();
		/* $result = select_query("tblquotes", "stage,COUNT(*)", "userid=" . (int) $userid . " GROUP BY stage"); */
		$result =\App\Models\Quote::where('userid',(int) $userid)->selectRaw("stage,COUNT(*) as count")->groupBy('stage')->get();
		foreach($result as $r){
			$quotestats[$r->stage] = $r->count;
		}

		$stats["numacceptedquotes"] = isset($quotestats["Accepted"]) ? $quotestats["Accepted"] : 0;
		$stats["numquotes"] = 0;
		foreach ($quotestats as $count) {
			$stats["numquotes"] += $count;
		}
		$statusfilter = array();
		$result=\App\Models\Ticketstatus::where('showactive',1)->select('title')->get();
		foreach($result as $r){
			$statusfilter[] = $r->title;
		}
		$ticketstats = array();
		//$result = select_query("tbltickets", "status,COUNT(*)", "userid=" . (int) $userid . " AND merged_ticket_id = 0 GROUP BY status");

		$result=\App\Models\Ticket::where('userid',(int) $userid)->where('merged_ticket_id',0)->selectRaw("status,COUNT(*) as count")->groupBy('status')->get();
		foreach($result as $r){
			$ticketstats[$r->status] = $r->count;
		}
		$stats["numtickets"] = 0;
		$stats["numactivetickets"] = $stats["numtickets"];
		foreach ($ticketstats as $status => $count) {
			if (in_array($status, $statusfilter)) {
				$stats["numactivetickets"] += $count;
			}
			$stats["numtickets"] += $count;
		}
		$result=DB::table("{$prefix}affiliatesaccounts")
				->join("{$prefix}affiliates","{$prefix}affiliatesaccounts.affiliateid","=","{$prefix}affiliates.id")
				->where('clientid',(int) $userid)
				->count();
		$stats["numaffiliatesignups"] = $result;
		$stats["isAffiliate"] = \App\Models\Affiliate::where('clientid',(int) $userid)->count() ? true : false;
		
		return $stats;
	}

	public static function getClientsDetails($userid = "", $contactid = "")
    {
        $auth = Auth::user();
        if (!$userid) {
            $userid = $auth ? $auth->id : 0;
        }
		$client = new \App\Helpers\ClientClass($userid);
        $details = $client->getDetails($contactid);
        return $details;
	}
	
	public static function outputClientLink($userid, $firstname = "", $lastname = "", $companyname = "", $groupid = "", $newWindow = false)
    {
        static $clientgroups = "";
        static $ClientOutputData = [];
        static $ContactOutputData = [];
        $contactid = 0;
        
        if (is_array($userid)) list($userid, $contactid) = $userid;
        if (!is_array($clientgroups)) $clientgroups = self::getClientGroups();

        if (!$firstname && !$lastname && !$companyname) {
            if (isset($ClientOutputData[$userid])) {
                $data = $ClientOutputData[$userid];
            } else {
                $data = \App\Models\Client::select("firstname", "lastname", "companyname", "groupid")->find($userid)->toArray();                
                $ClientOutputData[$userid] = $data;
            }

            $firstname = $data["firstname"];
            $lastname = $data["lastname"];
            $companyname = $data["companyname"];
            $groupid = $data["groupid"];
            
            if ($contactid) {
                if (isset($ContactOutputData[$contactid])) {
                    $contactdata = $ContactOutputData[$contactid];
                } else {
                    $contactdata = \App\Models\Contact::select("id", "userid", "firstname", "lastname")->where([["id" => $contactid], ["userid" => $userid]])->first()->toArray();
                    $ContactOutputData[$contactid] = $contactdata;
                }

                $firstname = $contactdata["firstname"];
                $lastname = $contactdata["lastname"];
            }
        }

        $style = isset($clientgroups[$groupid]["colour"]) ? " style=\"background-color:" .$clientgroups[$groupid]["colour"] ."\"" : "";
        $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid]);
        $clientlink = "<a href=\"$route\" $style " .($newWindow ? "target=\"_blank\"" : "")  .">";

        switch (\App\Helpers\Cfg::get("ClientDisplayFormat")) {
            case 2:
                $clientlink .= $companyname ?? "$firstname $lastname";
                break;
            case 3:
                $clientlink .= "$firstname $lastname";
                if ($companyname) {
                    $clientlink .= " ($companyname) ";
                }
                break;
            default:
                $clientlink .= "$firstname $lastname";
                break;
        }
        
        return $clientlink .= "</a>";
    }

    public static function getClientGroups()
    {
        $retarray = [];
        $result = \App\Models\Clientgroup::orderBy("groupname", "ASC")->get()->toArray();

        foreach ($result as $data) {
            $retarray[$data["id"]] = [
                                        "id" => $data["id"],
                                        "name" => $data["groupname"], 
                                        "colour" => $data["groupcolour"], 
                                        "discountpercent" => $data["discountpercent"], 
                                        "susptermexempt" => $data["susptermexempt"], 
                                        "separateinvoices" => $data["separateinvoices"]
                                    ];
        }

        return $retarray;
    }

	/**
	 * addClient
	 */
	public static function addClient($firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $securityqid = 0, $securityqans = "", $sendemail = true, array $additionalData = array(), $uuid = "", $isAdmin = false, $marketingOptIn = NULL)
	{
		global $remote_ip;
		$remote_ip = request()->ip();
		$verifyEmailAddress = Cfg::getValue("EnableEmailVerification");
		if (!$country) {
			$country = Cfg::getValue("DefaultCountry");
		}
		if (!$uuid) {
			$uuid = (string) Str::uuid();
		}
		$fullhost = gethostbyaddr($remote_ip);
		$currency = is_array(session()->get('currency')) ? session()->get('currency') : \App\Helpers\Format::getCurrency("", session()->get('currency'));
		$hasher = new \App\Helpers\Password();
		$password_hash = $hasher->hash(\App\Helpers\Sanitize::decode($password));
		$table = "tblclients";
		$array = array("uuid" => $uuid, "firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "password" => $password_hash, "lastlogin" => \Carbon\Carbon::now(), "securityqid" => $securityqid, "securityqans" => (new \App\Helpers\Pwd())->encrypt($securityqans), "ip" => $remote_ip, "host" => $fullhost, "status" => "Active", "datecreated" => \Carbon\Carbon::now(), "language" => session()->get('Language', ''), "currency" => $currency["id"], "email_verified" => 0, "created_at" => \Carbon\Carbon::now());
		$uid = \App\Models\Client::insertGetId($array);
		LogActivity::Save("Created Client " . $firstname . " " . $lastname . " - User ID: " . $uid, $uid);
		if (!empty($additionalData)) {
			$legacyBooleanColumns = array("taxexempt", "latefeeoveride", "overideduenotices", "separateinvoices", "disableautocc", "emailoptout", "overrideautoclose");
			foreach ($legacyBooleanColumns as $column) {
				if (isset($additionalData[$column])) {
					$additionalData[$column] = (bool) $additionalData[$column];
				}
			}
			if (!empty($additionalData["credit"]) && $additionalData["credit"] <= 0) {
				unset($additionalData["credit"]);
			}
			$tableData = $additionalData;
			if (isset($tableData["customfields"]) && !empty($tableData["customfields"])) {
				unset($tableData["customfields"]);
			}
			if (\App\Helpers\Vat::isTaxIdDisabled() || !\App\Helpers\Vat::isUsingNativeField()) {
				unset($tableData["tax_id"]);
			}
			
			// unset($tableData["customfields"]);
			\App\Models\Client::where(array("id" => $uid))->update($tableData);
			if (!empty($tableData["credit"])) {
				\App\Models\Credit::insert(array("clientid" => $uid, "date" => \Carbon\Carbon::now()->format("Y-m-d"), "description" => "Opening Credit Balance", "amount" => $tableData["credit"]));
			}
		}
		if (\App\Helpers\Application::isAdminAreaRequest() || defined("ADMINAREA")) {
			$isAdmin = true;
		}
		$request = request()->all();
		$customFields = $request['customfield'] ?? "";
		if (empty($customFields) && !empty($additionalData["customfields"])) {
			$customFields = $additionalData["customfields"];
		}
		\App\Helpers\Customfield::saveCustomFields($uid, $customFields, "client", $isAdmin);
		$client = \App\User\Client::find($uid);
		if (!is_null($marketingOptIn)) {
			if ($marketingOptIn) {
				$client->marketingEmailOptIn($remote_ip, false);
			} else {
				$client->marketingEmailOptOut($remote_ip, false);
			}
		}
		if ($verifyEmailAddress == "on") {
			if (!is_null($client)) {
				$client->sendEmailAddressVerification();
				// $client->sendEmailVerificationNotification();
			}
		} else {
			if ($sendemail) {
				\App\Helpers\Functions::sendMessage("Client Signup Email", $uid, array("client_password" => $password));
			}
		}
		if (\App\Helpers\Application::isClientAreaRequest() || defined("CLIENTAREA")) {
			Auth::guard('web')->loginUsingId($uid);
			session()->put("uid", $uid);
			// $_SESSION["uid"] = $uid;
			// $_SESSION["upw"] = WHMCS\Authentication\Client::generateClientLoginHash($uid, NULL, $password_hash);
			// $_SESSION["tkval"] = genRandomVal();
			Hooks::run_hook("ClientLogin", array("userid" => $uid, "contactid" => 0));
		}
		if (Cfg::getValue("TaxEUTaxValidation")) {
			$taxExempt = \App\Helpers\Vat::setTaxExempt($client);
			$client->save();
			if ($taxExempt != $additionalData["taxexempt"]) {
				$additionalData["taxexempt"] = $taxExempt;
			}
		}
		if (!\App\Helpers\Application::isApiRequest() || !defined("APICALL")) {
			Hooks::run_hook("ClientAdd", array_merge(array("userid" => $uid, "firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "password" => $password), $additionalData, array("customfields" => $customFields)));
		}
		return $uid;
	}

	public static function addContact($userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password = "", $permissions = array(), $generalemails = "", $productemails = "", $domainemails = "", $invoiceemails = "", $supportemails = "", $affiliateemails = "", $taxId = "")
	{
		if (!$country) {
			$country = \App\Helpers\Cfg::getValue("DefaultCountry");
		}
		$subaccount = $password ? "1" : "0";
		if ($permissions) {
			$permissions = implode(",", $permissions);
		}
		$table = "tblcontacts";
		$hasher = new \App\Helpers\Password();
		$password = \App\Helpers\Sanitize::decode($password);
		$array = array("userid" => $userid, "firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "tax_id" => $taxId, "subaccount" => $subaccount, "password" => $hasher->hash($password), "permissions" => $permissions, "generalemails" => $generalemails, "productemails" => $productemails, "domainemails" => $domainemails, "invoiceemails" => $invoiceemails, "supportemails" => $supportemails, "affiliateemails" => $affiliateemails);
		$contactid = \App\Models\Contact::insertGetId($array);
		\App\Helpers\Hooks::run_hook("ContactAdd", array_merge($array, array("contactid" => $contactid, "password" => $password)));
		\App\Helpers\LogActivity::Save("Added Contact - User ID: " . $userid . " - Contact ID: " . $contactid, $userid);
		return $contactid;
	}

    public static function getCountriesDropDown($selected = "", $fieldname = "", $tabindex = "", $selectInline = true, $disable = false)
    {
        global $CONFIG;
        global $_LANG;
        if (!$selected) {
            $selected = $CONFIG["DefaultCountry"];
        }
        if (!$fieldname) {
            $fieldname = "country";
        }
        if ($tabindex) {
            $tabindex = " tabindex=\"" . $tabindex . "\"";
        }
        if ($disable) {
            $disable = " disabled";
        } else {
            $disable = "";
        }
        $countries = new \App\Helpers\Country();
        $selectInlineClass = $selectInline ? " select-inline" : "";
        $dropdowncode = "<select name=\"" . $fieldname . "\" id=\"" . $fieldname . "\" class=\"form-control" . $selectInlineClass . "\"" . $tabindex . $disable . ">";
        foreach ($countries->getCountryNameArray() as $countriesvalue1 => $countriesvalue2) {
            $dropdowncode .= "<option value=\"" . $countriesvalue1 . "\"";
            if ($countriesvalue1 == $selected) {
                $dropdowncode .= " selected=\"selected\"";
            }
            $dropdowncode .= ">" . $countriesvalue2 . "</option>";
        }
        $dropdowncode .= "</select>";
        return $dropdowncode;
    }

	public static function validateClientLogin($username, $password)
	{
		$email = $username;
		$client = \App\Models\Client::where('email', $email)->where("status", "!=", "Closed")->first();
        if (!$client) {
            return false;
        }
        $clientPassword = $client->password;
        if (!Hash::check($password, $clientPassword)) {
            return false;
        }
		return true;

		// $authentication = new \App\Helpers\AuthenticationClient($username, $password);
		// if ($authentication::isInSecondFactorRequestState()) {
		// 	if (!$authentication->verifySecondFactor()) {
		// 		return false;
		// 	}
		// 	$authentication->finalizeLogin();
		// 	return true;
		// }
		// if ($authentication->verifyFirstFactor()) {
		// 	if (!$authentication->needsSecondFactorToFinalize()) {
		// 		$authentication->finalizeLogin();
		// 		return true;
		// 	}
		// 	$authentication->prepareSecondFactor();
		// }
		// return false;
	}
	
	public static function initialiseLoggedInClient($email)
	{
		global $clientsdetails;
		$client = \App\Models\Client::where('email', $email)->where("status", "!=", "Closed")->first();
		$clientAlerts = array();
		$clientsdetails = array();
		$clientsstats = array();
		$loggedinuser = array();
		$contactpermissions = array();
		$emailVerificationPending = false;
		$clientId = NULL;
		if ($client) {
			$clientId = $client->id;
			$client = \App\Models\Client::find($clientId);
			$legacyClient = new \App\Helpers\ClientClass($client);
			$clientsdetails = $legacyClient->getDetails();
			$clientsstats = (new self)->getClientsStats($clientId);
			$contactid = (int) session("cid");
			if ($contactid) {
				$contactdata = \App\Models\Contact::where("id", $contactid)->where("userid", $clientId)->first();
				if ($contactdata) {
					$loggedinuser = array("contactid" => $contactdata->getAttribute("id"), "firstname" => $contactdata->getAttribute("firstname"), "lastname" => $contactdata->getAttribute("lastname"), "email" => $contactdata->getAttribute("email"));
					$contactpermissions = explode(",", $contactdata["permissions"]);
				}
			} else {
				$loggedinuser = array("userid" => $clientId, "firstname" => $clientsdetails["firstname"], "lastname" => $clientsdetails["lastname"], "email" => $clientsdetails["email"]);
				$contactpermissions = array("profile", "contacts", "products", "manageproducts", "domains", "managedomains", "invoices", "tickets", "affiliates", "emails", "orders");
			}
			// TODO: this
			// $alerts = new WHMCS\User\Client\AlertFactory($client);
			// $clientAlerts = $alerts->build();
			if (\App\Helpers\Cfg::getValue("EnableEmailVerification")) {
				$emailVerificationPending = !$client->isEmailAddressVerified();
			}
			Auth::guard('web')->loginUsingId($clientId);
		}
		$smartyvalues["loggedin"] = (bool) $clientId;
		$smartyvalues["client"] = $client;
		$smartyvalues["clientsdetails"] = $clientsdetails;
		$smartyvalues["clientAlerts"] = $clientAlerts;
		$smartyvalues["clientsstats"] = $clientsstats;
		$smartyvalues["loggedinuser"] = $loggedinuser;
		$smartyvalues["contactpermissions"] = $contactpermissions;
		$smartyvalues["emailVerificationPending"] = $emailVerificationPending;
		return $smartyvalues;
	}

	public static function checkContactPermission($requiredPermission, $noRedirect = false)
	{
		if (session("cid")) {
			$contact = \App\Models\Contact::find(session("cid"));
			$permissions = $contact->permissions;
			if (!in_array($requiredPermission, $permissions)) {
				global $ca;
				global $_LANG;
				global $smartyvalues;
				if ($noRedirect) {
					return false;
				}
				foreach ($permissions as $key => $permission) {
					$permissions[$key] = \Lang::get("subaccountperms" . $permission);
				}
				// Menu::primarySidebar("clientView");
				// Menu::secondarySidebar("support");
				if (is_object($ca)) {
					// $ca->setDisplayTitle(\Lang::get("accessdenied"));
					// $ca->assign("allowedpermissions", $permissions);
					// $ca->assign("requiredpermission", $reqperm);
					// $ca->setTemplate("contactaccessdenied");
					// $ca->output();
					// exit;
				}
				$smartyvalues["allowedpermissions"] = $permissions;
				// $smartyvalues["requiredpermission"] = $reqperm;
				$templatefile = "contactaccessdenied";
				// outputClientArea($templatefile);
				// exit;
			}
		}
		return true;
	}
}
