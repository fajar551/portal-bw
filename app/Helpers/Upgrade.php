<?php
namespace App\Helpers;

use DB, Auth;

// Import Model Class here

// Import Package Class here
use App\Helpers\Hooks;
use App\Helpers\Cfg;
use App\Helpers\Application;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Upgrade
{
	/**
	 * upgradeAlreadyInProgress
	 */
	public static function upgradeAlreadyInProgress($hostingId)
	{
		$hostingId = (int) $hostingId;
		$hostingSQL = "SELECT tblinvoices.status FROM tblorders, tblupgrades, tblinvoices WHERE tblupgrades.relid = '%d' AND tblorders.id = tblupgrades.orderid AND tblorders.invoiceid = tblinvoices.id AND tblinvoices.status = 'Unpaid'";
		$sql = DB::raw(sprintf($hostingSQL, $hostingId));
		$result = DB::select($sql);

		if ($result && $result[0]->status) {
			return true;
		}
		return false;
	}

	/**
	 * SumUpPackageUpgradeOrder
	 */
	public static function SumUpPackageUpgradeOrderOLD($id, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod = "", $checkout = "")
	{
        $auth = Auth::user();
        if (Application::isAdminAreaRequest()) {
            $auth = new \stdClass();
            $auth->id = $GLOBALS["uid"];
        }

		global $CONFIG;
		global $_LANG;
		global $currency;
		global $upgradeslist;
		global $orderamount;
		global $orderdescription;
		global $applytax;
		$sessionUpgradeids['upgradeids'] = [];
		$configoptionsamount = 0;
		$amountToCredit = 0;
		$result = \App\Models\Hosting::select("tblproducts.id", "tblproducts.name", "tblhosting.nextduedate", "tblhosting.billingcycle", "tblhosting.amount", "tblhosting.firstpaymentamount", "tblhosting.domain")
			->where("userid", $auth->id)
			->where("tblhosting.id", $id)
			->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
			->first();
		$data = $result->toArray();
		$oldproductid = $data["id"];
		$oldproductname = \App\Models\Product::getProductName($oldproductid, $data["name"]);
		$domain = $data["domain"];
		$nextduedate = $data["nextduedate"];
		$billingcycle = $data["billingcycle"];
		$oldamount = $data["amount"];
		if ($billingcycle == "One Time") {
			$oldamount = $data["firstpaymentamount"];
		}
		$cycle = new \App\Helpers\Cycles();
		if (!($cycle->isValidSystemBillingCycle($newproductbillingcycle) || $cycle->isValidPublicBillingCycle($newproductbillingcycle))) {
			throw new \Exception("Invalid New Billing Cycle");
		}
		if (Application::isClientAreaRequest()) {
			try {
				$currentProduct = \App\Models\Product::findOrFail($oldproductid);
				$upgradeProductIds = $currentProduct->upgradeProducts()->pluck("upgrade_product_id");
			} catch (\Exception $e) {
				throw new \App\Exceptions\Fatal("Invalid Current Product ID");
			}
			if (!$upgradeProductIds->contains($newproductid)) {
				throw new \App\Exceptions\Fatal("Invalid new product ID for upgrade");
			}
		}
		try {
			$product = \App\Models\Product::findOrFail($newproductid);
			$newproductid = $product->id;
			$newproductname = $product->name;
			$applytax = $product->applyTax;
			$paytype = $product->paymentType;
			$stockControlEnabled = $product->stockControlEnabled;
			$quantityInStock = $product->quantityInStock;
		} catch (\Exception $e) {
			throw new \App\Exceptions\Fatal("Invalid New Product ID");
		}
		if ($stockControlEnabled && $quantityInStock <= 0 && $oldproductid != $newproductid) {
			throw new \App\Exceptions\Fatal("Product Out of Stock");
		}
		$normalisedBillingCycle = $cycle->getNormalisedBillingCycle($newproductbillingcycle);
		if (!in_array($normalisedBillingCycle, $product->getAvailableBillingCycles())) {
			throw new \App\Exceptions\Fatal("Invalid Billing Cycle Requested");
		}
		$newproductbillingcycleraw = $newproductbillingcycle;
		$newproductbillingcyclenice = ucfirst($newproductbillingcycle);
		if ($newproductbillingcyclenice == "Semiannually") {
			$newproductbillingcyclenice = "Semi-Annually";
		}
		$configoptionspricingarray = \App\Helpers\ConfigOptions::getCartConfigOptions($newproductid, "", $newproductbillingcyclenice, $id);
		if ($configoptionspricingarray) {
			foreach ($configoptionspricingarray as $configoptionkey => $configoptionvalues) {
				$configoptionsamount += $configoptionvalues["selectedrecurring"];
			}
		}
		$newproductbillingcycle = $normalisedBillingCycle;
		if ($newproductbillingcycle == "onetime") {
			$newproductbillingcycle = "monthly";
		}
		if ($newproductbillingcycle == "free") {
			$newamount = 0;
		} else {
			$result = \App\Models\Pricing::select($newproductbillingcycle)->where("type", "product")->where("currency", $currency["id"])->where("relid", $newproductid)->first();
			$data = $result->toArray();
			$newamount = $data[$newproductbillingcycle];
		}
		if (($paytype == "onetime" || $paytype == "recurring") && $newamount < 0) {
			throw new \Exception("Invalid New Billing Cycle");
		}
		$newamount += $configoptionsamount;
		$year = substr($nextduedate, 0, 4);
		$month = substr($nextduedate, 5, 2);
		$day = substr($nextduedate, 8, 2);
		$oldCycleMonths = \App\Helpers\Invoice::getBillingCycleMonths($billingcycle);     // Noted
		$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $oldCycleMonths, $day, $year));
		$totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
        $newCycleMonths = \App\Helpers\Invoice::getBillingCycleMonths($newproductbillingcyclenice);   // Noted
		$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $newCycleMonths, $day, $year));
		$newtotaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
		if ($newproductbillingcyclenice == "Onetime") {
			$newtotaldays = $totaldays;
		}
		if ($billingcycle == "Free Account" || $billingcycle == "One Time") {
			$days = $newtotaldays = $totaldays = \App\Helpers\Invoice::getBillingCycleDays($newproductbillingcyclenice);
			$totalmonths = \App\Helpers\Invoice::getBillingCycleMonths($newproductbillingcyclenice);
			$nextduedate = date("Y-m-d", mktime(0, 0, 0, date("m") + $totalmonths, date("d"), date("Y")));
			$amountdue = \App\Helpers\Functions::format_as_currency($newamount - $oldamount);
			$difference = $newamount;
		} else {
			$todaysdate = date("Ymd");
			$nextduedatetime = strtotime($nextduedate);
			$todaysdate = strtotime($todaysdate);
			$days = round(($nextduedatetime - $todaysdate) / 86400);
			$oldAmountPerMonth = round($oldamount / $oldCycleMonths, 2);
			$newAmountPerMonth = round($newamount / $newCycleMonths, 2);
			if ($oldAmountPerMonth == $newAmountPerMonth) {
				$newamount = $oldamount / $totaldays * $newtotaldays;
			}
			$daysnotused = $days / $totaldays;
			$refundamount = $oldamount * $daysnotused;
			$cyclemultiplier = $days / $newtotaldays;
			$amountdue = $newamount * $cyclemultiplier;
			$amountdue = $amountdue - $refundamount;
			if ($amountdue < 0 && !$CONFIG["CreditOnDowngrade"]) {
				$amountToCredit = $amountdue;
				$amountdue = 0;
			}
			$amountdue = \App\Helpers\Functions::format_as_currency($amountdue);
			$difference = $newamount - $oldamount;
		}
		$discount = 0;
		$promoqualifies = true;
		if ($promocode) {
			$promodata = self::validateUpgradePromo($promocode);
			if (is_array($promodata)) {
				$appliesto = $promodata["appliesto"];
				$requires = $promodata["requires"];
				$cycles = $promodata["cycles"];
				$value = $promodata["value"];
				$type = $promodata["discounttype"];
				$promodesc = $promodata["desc"];
				if ($newproductbillingcycle == "free") {
					$billingcycle = "Free Account";
				} else {
					if ($newproductbillingcycle == "onetime") {
						$billingcycle = "One Time";
					} else {
						if ($newproductbillingcycle == "semiannually") {
							$billingcycle = "Semi-Annually";
						} else {
							$billingcycle = ucfirst($newproductbillingcycle);
						}
					}
				}
				if (count($appliesto) && $appliesto[0] && !in_array($newproductid, $appliesto)) {
					$promoqualifies = false;
				}
				if (count($requires) && $requires[0] && !in_array($oldproductid, $requires)) {
					$promoqualifies = false;
				}
				if (count($cycles) && $cycles[0] && !in_array($billingcycle, $cycles)) {
					$promoqualifies = false;
				}
				if ($promoqualifies && 0 < $amountdue) {
					if ($type == "Percentage") {
						$percent = $value / 100;
						$discount = $amountdue * $percent;
					} else {
						$discount = $value;
						if ($amountdue < $discount) {
							$discount = $amountdue;
						}
					}
				}
			}
			if ($discount == 0) {
				$promodata = \App\Models\Promotion::where('lifetimepromo', 1)->where('recurring', 1)->where('code', $promocode)->first();
                if ($promodata) {
                    $promodata = $promodata->toArray();
                    if (is_array($promodata)) {
                        if ($promodata["type"] == "Percentage") {
                            $percent = $promodata["value"] / 100;
                            $discount = $amountdue * $percent;
                        } else {
                            $discount = $promodata["value"];
                            if ($amountdue < $discount) {
                                $discount = $amountdue;
                            }
                        }
                        $promoqualifies = true;
                    }
                }
			}
		}
		$upgradearray[] = array("oldproductid" => $oldproductid, "oldproductname" => $oldproductname, "newproductid" => $newproductid, "newproductname" => $newproductname, "daysuntilrenewal" => $days, "totaldays" => $totaldays, "newproductbillingcycle" => $newproductbillingcycleraw, "price" => $amountdue, "discount" => $discount, "promoqualifies" => $promoqualifies);
		$hookReturns = Hooks::run_hook("OrderProductUpgradeOverride", $upgradearray[0]);
		foreach ($hookReturns as $hookReturn) {
			if (is_array($hookReturn)) {
				if (isset($hookReturn["price"])) {
					$upgradearray[0]["price"] = $hookReturn["price"];
					$amountdue = $upgradearray[0]["price"];
				}
				if (isset($hookReturn["discount"])) {
					$discount = $hookReturn["discount"];
				}
				if (isset($hookReturn["promoqualifies"])) {
					if (!is_bool($hookReturn["promoqualifies"])) {
						throw new \App\Exceptions\Fatal("Invalid promo qualification parameter returned by hook. " . "Must be boolean, returned " . gettype($hookReturn["promoqualifies"]));
					}
					$promoqualifies = $hookReturn["promoqualifies"];
				}
				if (isset($hookReturn["daysuntilrenewal"])) {
					$upgradearray[0]["daysuntilrenewal"] = $hookReturn["daysuntilrenewal"];
				}
				if (isset($hookReturn["totaldays"])) {
					$upgradearray[0]["totaldays"] = $hookReturn["totaldays"];
				}
				if (isset($hookReturn["newproductbillingcycle"])) {
					$upgradearray[0]["newproductbillingcycle"] = $hookReturn["newproductbillingcycle"];
				}
				try {
					if (isset($hookReturn["oldproductid"])) {
						$product = \App\Models\Product::findOrFail($oldproductid);
						$upgradearray[0]["oldproductname"] = $product->name;
					}
					if (isset($hookReturn["newproductid"])) {
						$product = \App\Models\Product::findOrFail($newproductid);
						$upgradearray[0]["newproductname"] = $product->name;
					}
				} catch (\Exception $e) {
					throw new \App\Exceptions\Fatal("Invalid Product ID returned by hook");
				}
			}
		}
		$upgradearray[0]["price"] = \App\Helpers\Format::formatCurrency($upgradearray[0]["price"]);
		unset($upgradearray[0]["discount"]);
		unset($upgradearray[0]["promoqualifies"]);
		$GLOBALS["subtotal"] = $amountdue;
		$GLOBALS["qualifies"] = $promoqualifies;
		$GLOBALS["discount"] = $discount;
		$client = \App\Models\Client::find($auth->id);
		$totalDue = $amountdue;
		if (Cfg::get("TaxEnabled") && $applytax && !$client->taxExempt) {
			$taxData = \App\Helpers\Invoice::getTaxRate(1, $client->state, $client->country);
			$taxRate = $taxData["rate"] / 100;
			$taxData = \App\Helpers\Invoice::getTaxRate(2, $client->state, $client->country);
			$taxRate2 = $taxData["rate"] / 100;
			if (Cfg::get("TaxType") == "Exclusive") {
				if (Cfg::get("TaxL2Compound")) {
					$totalDue += $totalDue * $taxRate;
					$totalDue += $totalDue * $taxRate2;
				} else {
					$totalDue += $totalDue * $taxRate + $totalDue * $taxRate2;
				}
			}
		}
		if ($checkout) {
			try {
                $orderdescription = $_LANG["upgradedowngradepackage"] . ": " . $oldproductname . " => " . $newproductname . "<br>\n" . $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($newproductbillingcycle))] . "<br>\n" . $_LANG["ordertotalduetoday"] . ": " . \App\Helpers\Format::formatCurrency($totalDue);
            } catch (\Throwable $th) {
                $orderdescription = __("client.upgradedowngradepackage") . ": " . $oldproductname . " => " . $newproductname . "<br>\n" . __("client.orderbillingcycle") . ": " . __("client.orderpaymentterm" . str_replace(array("-", " "), "", strtolower($newproductbillingcycle))) . "<br>\n" . __("client.ordertotalduetoday") . ": " . \App\Helpers\Format::formatCurrency($totalDue);
            }
            $amountwithdiscount = $amountdue - $discount;
			$upgradeid = \App\Models\Upgrade::insert(array("type" => "package", "date" => \Carbon\Carbon::now(), "relid" => $id, "originalvalue" => $oldproductid, "newvalue" => (string) $newproductid . "," . $newproductbillingcycleraw, "amount" => $amountwithdiscount, "recurringchange" => $difference));
			$upgradeslist .= $upgradeid . ",";
			$sessionUpgradeids['upgradeids'][] = $upgradeid;
			$hookReturns = Hooks::run_hook("PreUpgradeCheckout", array("clientId" => (int) $auth->id, "upgradeId" => $upgradeid, "serviceId" => $id, "amount" => $amountdue, "discount" => $discount));
			foreach ($hookReturns as $hookReturn) {
				if (is_array($hookReturn)) {
					if (array_key_exists("amount", $hookReturn) && is_numeric($hookReturn["amount"])) {
						$amountdue = $hookReturn["amount"];
					}
					if (array_key_exists("discount", $hookReturn) && is_numeric($hookReturn["discount"])) {
						$discount = $hookReturn["discount"];
					}
					$amountwithdiscount = $amountdue - $discount;
					DB::table("tblupgrades")->where("id", $upgradeid)->update(array("amount" => $amountwithdiscount));
				}
			}
			if (0 < $amountdue) {
				if ($domain) {
					$domain = " - " . $domain;
				}

                $upgradedowngradepackage = "";
                try {
                    $upgradedowngradepackage = $_LANG["upgradedowngradepackage"];
                } catch (\Throwable $th) {
                    $upgradedowngradepackage = __("client.upgradedowngradepackage");
                }
				\App\Models\Invoiceitem::insert(array("userid" => $auth->id, "type" => "Upgrade", "relid" => $upgradeid, "description" => $upgradedowngradepackage . ": " . $oldproductname . $domain . "\n" . $oldproductname . " => " . $newproductname . " " . "(" . \App\Helpers\Functions::getTodaysDate() . " - " . (new \App\Helpers\Client)->fromMySQLDate($nextduedate) . ")", "amount" => $amountdue, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
				
                $orderpromotioncode = "";
                try {
                    $orderpromotioncode = $_LANG["orderpromotioncode"];
                } catch (\Throwable $th) {
                    $orderpromotioncode = __("client.orderpromotioncode");
                }

                if (0 < $discount) {
					\App\Models\Invoiceitem::insert(array("userid" => $auth->id, "description" => $orderpromotioncode . ": " . $promocode . " - " . $promodesc, "amount" => $discount * -1, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
				}
				$orderamount += $amountwithdiscount;
			} else {
				if ($CONFIG["CreditOnDowngrade"]) {
					$creditamount = $amountdue * -1;
					\App\Models\Credit::insert(array("clientid" => $auth->id, "date" => \Carbon\Carbon::now(), "description" => "Upgrade/Downgrade Credit", "amount" => $creditamount));
					$c = \App\Models\Client::find($auth->id);
					if ($c) {
						$c->increment("credit", $creditamount);
					}
				} else {
					if ($amountToCredit) {
						session(["UpgradeCredit" . $upgradeid => $amountToCredit]);
					}
				}
				\App\Models\Upgrade::where('id', $upgradeid)->update(array("paid" => "Y"));
				self::doUpgrade($upgradeid);
			}
		}
		session($sessionUpgradeids);
		return $upgradearray;
	}

    /**
     * SumUpPackageUpgradeOrder
     */
    public static function SumUpPackageUpgradeOrder($id, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod = "", $checkout = "")
    {
        global $CONFIG;
        global $_LANG;
        global $currency;
        global $upgradeslist;
        global $orderamount;
        global $orderdescription;
        global $applytax;
        $auth = Auth::user();
        if (Application::isAdminAreaRequest()) {
            $auth = new \stdClass();
            $auth->id = $GLOBALS["uid"];
        }
        
        // $_SESSION["upgradeids"] = array();
        session()->put("upgradeids", []);
        // $whmcs = App::self();
        $configoptionsamount = 0;
        $amountToCredit = 0;
        $result = \App\Models\Hosting::selectRaw("tblproducts.id,tblproducts.name,tblhosting.nextduedate,tblhosting.billingcycle,tblhosting.amount," . "tblhosting.firstpaymentamount,tblhosting.domain")
            ->where(array("userid" => $auth->id, "tblhosting.id" => $id))
            ->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
            ->first();
        $data = $result->toArray();
        $oldproductid = $data["id"];
        $oldproductname = \App\Models\Product::getProductName($oldproductid, $data["name"]);
        $domain = $data["domain"];
        $nextduedate = $data["nextduedate"];
        $billingcycle = $data["billingcycle"];
        $oldamount = $data["amount"];
        if ($billingcycle == "One Time") {
            $oldamount = $data["firstpaymentamount"];
        }
        $cycle = new \App\Helpers\Cycles();
        if (!($cycle->isValidSystemBillingCycle($newproductbillingcycle) || $cycle->isValidPublicBillingCycle($newproductbillingcycle))) {
            throw new \Exception("Invalid New Billing Cycle");
        }
        if (defined("CLIENTAREA")) {
            try {
                $currentProduct = \App\Models\Product::findOrFail($oldproductid);
                $upgradeProductIds = $currentProduct->upgradeProducts()->pluck("upgrade_product_id");
            } catch (\Exception $e) {
                throw new \App\Exceptions\Fatal("Invalid Current Product ID");
            }
            if (!$upgradeProductIds->contains($newproductid)) {
                throw new \App\Exceptions\Fatal("Invalid new product ID for upgrade");
            }
        }
        try {
            $product = \App\Models\Product::findOrFail($newproductid);
            $newproductid = $product->id;
            $newproductname = $product->name;
            $applytax = $product->applyTax;
            $paytype = $product->paymentType;
            $stockControlEnabled = $product->stockControlEnabled;
            $quantityInStock = $product->quantityInStock;
        } catch (\Exception $e) {
            throw new \App\Exceptions\Fatal("Invalid New Product ID");
        }
        if ($stockControlEnabled && $quantityInStock <= 0 && $oldproductid != $newproductid) {
            throw new \App\Exceptions\Fatal("Product Out of Stock");
        }
        $normalisedBillingCycle = $cycle->getNormalisedBillingCycle($newproductbillingcycle);
        if (!in_array($normalisedBillingCycle, $product->getAvailableBillingCycles())) {
            throw new \App\Exceptions\Fatal("Invalid Billing Cycle Requested");
        }
        $newproductbillingcycleraw = $newproductbillingcycle;
        $newproductbillingcyclenice = ucfirst($newproductbillingcycle);
        if ($newproductbillingcyclenice == "Semiannually") {
            $newproductbillingcyclenice = "Semi-Annually";
        }
        $configoptionspricingarray = \App\Helpers\ConfigOptions::getCartConfigOptions($newproductid, "", $newproductbillingcyclenice, $id);
        if ($configoptionspricingarray) {
            foreach ($configoptionspricingarray as $configoptionkey => $configoptionvalues) {
                $configoptionsamount += $configoptionvalues["selectedrecurring"];
            }
        }
        $newproductbillingcycle = $normalisedBillingCycle;
        if ($newproductbillingcycle == "onetime") {
            $newproductbillingcycle = "monthly";
        }
        if ($newproductbillingcycle == "free") {
            $newamount = 0;
        } else {
            $result = \App\Models\Pricing::select($newproductbillingcycle)->where(array("type" => "product", "currency" => $currency["id"], "relid" => $newproductid))->first();
            $data = $result->toArray();
            $newamount = $data[$newproductbillingcycle];
        }
        if (($paytype == "onetime" || $paytype == "recurring") && $newamount < 0) {
            throw new \Exception("Invalid New Billing Cycle");
        }
        $newamount += $configoptionsamount;
        $year = substr($nextduedate, 0, 4);
        $month = substr($nextduedate, 5, 2);
        $day = substr($nextduedate, 8, 2);
        $oldCycleMonths = \App\Helpers\Invoice::getBillingCycleMonths($billingcycle);
        $prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $oldCycleMonths, $day, $year));
        $totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
        $newCycleMonths = \App\Helpers\Invoice::getBillingCycleMonths($newproductbillingcyclenice);
        $prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $newCycleMonths, $day, $year));
        $newtotaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
        if ($newproductbillingcyclenice == "Onetime") {
            $newtotaldays = $totaldays;
        }
        if ($billingcycle == "Free Account" || $billingcycle == "One Time") {
            $days = $newtotaldays = $totaldays = \App\Helpers\Invoice::getBillingCycleDays($newproductbillingcyclenice);
            $totalmonths = \App\Helpers\Invoice::getBillingCycleMonths($newproductbillingcyclenice);
            $nextduedate = date("Y-m-d", mktime(0, 0, 0, date("m") + $totalmonths, date("d"), date("Y")));
            $amountdue = \App\Helpers\Functions::format_as_currency($newamount - $oldamount);
            $difference = $newamount;
        } else {
            $todaysdate = date("Ymd");
            $nextduedatetime = strtotime($nextduedate);
            $todaysdate = strtotime($todaysdate);
            $days = round(($nextduedatetime - $todaysdate) / 86400);
            $oldAmountPerMonth = round($oldamount / $oldCycleMonths, 2);
            $newAmountPerMonth = round($newamount / $newCycleMonths, 2);
            if ($oldAmountPerMonth == $newAmountPerMonth) {
                $newamount = $oldamount / $totaldays * $newtotaldays;
            }
            $daysnotused = $days / $totaldays;
            $refundamount = $oldamount * $daysnotused;
            $cyclemultiplier = $days / $newtotaldays;
            $amountdue = $newamount * $cyclemultiplier;
            $amountdue = $amountdue - $refundamount;
            if ($amountdue < 0 && !$CONFIG["CreditOnDowngrade"]) {
                $amountToCredit = $amountdue;
                $amountdue = 0;
            }
            $amountdue = \App\Helpers\Functions::format_as_currency($amountdue);
            $difference = $newamount - $oldamount;
        }
        $discount = 0;
        $promoqualifies = true;
        if ($promocode) {
            $promodata = self::validateUpgradePromo($promocode);
            if (is_array($promodata)) {
                $appliesto = $promodata["appliesto"];
                $requires = $promodata["requires"];
                $cycles = $promodata["cycles"];
                $value = $promodata["value"];
                $type = $promodata["discounttype"];
                $promodesc = $promodata["desc"];
                if ($newproductbillingcycle == "free") {
                    $billingcycle = "Free Account";
                } else {
                    if ($newproductbillingcycle == "onetime") {
                        $billingcycle = "One Time";
                    } else {
                        if ($newproductbillingcycle == "semiannually") {
                            $billingcycle = "Semi-Annually";
                        } else {
                            $billingcycle = ucfirst($newproductbillingcycle);
                        }
                    }
                }
                if (count($appliesto) && $appliesto[0] && !in_array($newproductid, $appliesto)) {
                    $promoqualifies = false;
                }
                if (count($requires) && $requires[0] && !in_array($oldproductid, $requires)) {
                    $promoqualifies = false;
                }
                if (count($cycles) && $cycles[0] && !in_array($billingcycle, $cycles)) {
                    $promoqualifies = false;
                }
                if ($promoqualifies && 0 < $amountdue) {
                    if ($type == "Percentage") {
                        $percent = $value / 100;
                        $discount = $amountdue * $percent;
                    } else {
                        $discount = $value;
                        if ($amountdue < $discount) {
                            $discount = $amountdue;
                        }
                    }
                }
            }
            if ($discount == 0) {
                $promodata = \App\Models\Promotion::where(array("lifetimepromo" => 1, "recurring" => 1, "code" => $promocode))->first();
                $promodata = $promodata ? $promodata->toArray() : "";
                if (is_array($promodata)) {
                    if ($promodata["type"] == "Percentage") {
                        $percent = $promodata["value"] / 100;
                        $discount = $amountdue * $percent;
                    } else {
                        $discount = $promodata["value"];
                        if ($amountdue < $discount) {
                            $discount = $amountdue;
                        }
                    }
                    $promoqualifies = true;
                }
            }
        }
        $upgradearray[] = array("oldproductid" => $oldproductid, "oldproductname" => $oldproductname, "newproductid" => $newproductid, "newproductname" => $newproductname, "daysuntilrenewal" => $days, "totaldays" => $totaldays, "newproductbillingcycle" => $newproductbillingcycleraw, "price" => $amountdue, "discount" => $discount, "promoqualifies" => $promoqualifies);
        $hookReturns = Hooks::run_hook("OrderProductUpgradeOverride", $upgradearray[0]);
        foreach ($hookReturns as $hookReturn) {
            if (is_array($hookReturn)) {
                if (isset($hookReturn["price"])) {
                    $upgradearray[0]["price"] = $hookReturn["price"];
                    $amountdue = $upgradearray[0]["price"];
                }
                if (isset($hookReturn["discount"])) {
                    $discount = $hookReturn["discount"];
                }
                if (isset($hookReturn["promoqualifies"])) {
                    if (!is_bool($hookReturn["promoqualifies"])) {
                        throw new \App\Exceptions\Fatal("Invalid promo qualification parameter returned by hook. " . "Must be boolean, returned " . gettype($hookReturn["promoqualifies"]));
                    }
                    $promoqualifies = $hookReturn["promoqualifies"];
                }
                if (isset($hookReturn["daysuntilrenewal"])) {
                    $upgradearray[0]["daysuntilrenewal"] = $hookReturn["daysuntilrenewal"];
                }
                if (isset($hookReturn["totaldays"])) {
                    $upgradearray[0]["totaldays"] = $hookReturn["totaldays"];
                }
                if (isset($hookReturn["newproductbillingcycle"])) {
                    $upgradearray[0]["newproductbillingcycle"] = $hookReturn["newproductbillingcycle"];
                }
                try {
                    if (isset($hookReturn["oldproductid"])) {
                        $product = \App\Models\Product::findOrFail($oldproductid);
                        $upgradearray[0]["oldproductname"] = $product->name;
                    }
                    if (isset($hookReturn["newproductid"])) {
                        $product = \App\Models\Product::findOrFail($newproductid);
                        $upgradearray[0]["newproductname"] = $product->name;
                    }
                } catch (\Exception $e) {
                    throw new \App\Exceptions\Fatal("Invalid Product ID returned by hook");
                }
            }
        }
        $upgradearray[0]["price"] = \App\Helpers\Format::formatCurrency($upgradearray[0]["price"]);
        unset($upgradearray[0]["discount"]);
        unset($upgradearray[0]["promoqualifies"]);
        $GLOBALS["subtotal"] = $amountdue;
        $GLOBALS["qualifies"] = $promoqualifies;
        $GLOBALS["discount"] = $discount;
        $client = \App\Models\Client::find($auth->id);
        $totalDue = $amountdue;
        if (Cfg::get("TaxEnabled") && $applytax && !$client->taxExempt) {
            $taxData = \App\Helpers\Invoice::getTaxRate(1, $client->state, $client->country);
            $taxRate = $taxData["rate"] / 100;
            $taxData = \App\Helpers\Invoice::getTaxRate(2, $client->state, $client->country);
            $taxRate2 = $taxData["rate"] / 100;
            if (Cfg::get("TaxType") == "Exclusive") {
                if (Cfg::get("TaxL2Compound")) {
                    $totalDue += $totalDue * $taxRate;
                    $totalDue += $totalDue * $taxRate2;
                } else {
                    $totalDue += $totalDue * $taxRate + $totalDue * $taxRate2;
                }
            }
        }
        if ($checkout) {
            $orderdescription = $_LANG["upgradedowngradepackage"] . ": " . $oldproductname . " => " . $newproductname . "<br>\n" . $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($newproductbillingcycle))] . "<br>\n" . $_LANG["ordertotalduetoday"] . ": " . \App\Helpers\Format::formatCurrency($totalDue);
            $amountwithdiscount = $amountdue - $discount;
            $upgradeid = \App\Models\Upgrade::insertGetId(array("userid" => $auth->id, "type" => "package", "date" => \Carbon\Carbon::now(), "relid" => $id, "originalvalue" => $oldproductid, "newvalue" => (string) $newproductid . "," . $newproductbillingcycleraw, "amount" => $amountwithdiscount, "recurringchange" => $difference));
            $upgradeslist .= $upgradeid . ",";
            // $_SESSION["upgradeids"][] = $upgradeid;
            session()->push("upgradeids", $upgradeid);
            $hookReturns = Hooks::run_hook("PreUpgradeCheckout", array("clientId" => (int) $auth->id, "upgradeId" => $upgradeid, "serviceId" => $id, "amount" => $amountdue, "discount" => $discount));
            foreach ($hookReturns as $hookReturn) {
                if (is_array($hookReturn)) {
                    if (array_key_exists("amount", $hookReturn) && is_numeric($hookReturn["amount"])) {
                        $amountdue = $hookReturn["amount"];
                    }
                    if (array_key_exists("discount", $hookReturn) && is_numeric($hookReturn["discount"])) {
                        $discount = $hookReturn["discount"];
                    }
                    $amountwithdiscount = $amountdue - $discount;
                    DB::table("tblupgrades")->where("id", $upgradeid)->update(array("amount" => $amountwithdiscount));
                }
            }
            if (0 < $amountdue) {
                if ($domain) {
                    $domain = " - " . $domain;
                }
                \App\Models\Invoiceitem::insert(array("userid" => $auth->id, "type" => "Upgrade", "relid" => $upgradeid, "description" => $_LANG["upgradedowngradepackage"] . ": " . $oldproductname . $domain . "\n" . $oldproductname . " => " . $newproductname . " " . "(" . \App\Helpers\Functions::getTodaysDate() . " - " . (new \App\Helpers\Functions())->fromMySQLDate($nextduedate) . ")", "amount" => $amountdue, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
				if (0 < $discount) {
					\App\Models\Invoiceitem::insert(array("userid" => $auth->id, "description" => $_LANG["orderpromotioncode"] . ": " . $promocode . " - " . $promodesc, "amount" => $discount * -1, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
				}
                $orderamount += $amountwithdiscount;
            } else {
                if ($CONFIG["CreditOnDowngrade"]) {
                    $creditamount = $amountdue * -1;
					\App\Models\Credit::insert(array("clientid" => $auth->id, "date" => \Carbon\Carbon::now(), "description" => "Upgrade/Downgrade Credit", "amount" => $creditamount));
					$c = \App\Models\Client::find($auth->id);
					if ($c) {
						$c->increment("credit", $creditamount);
					}
                } else {
                    if ($amountToCredit) {
                        session()->put("UpgradeCredit".$upgradeid, $amountToCredit);
                    }
                }
                \App\Models\Upgrade::where(array("id" => $upgradeid))->update(array("paid" => "Y"));
                self::doUpgrade($upgradeid);
            }
        }
        return $upgradearray;
    }

	/**
	 * SumUpConfigOptionsOrder
	 */
	public static function SumUpConfigOptionsOrder($id, $configoptions, $promocode, $paymentmethod = "", $checkout = "")
	{
		$auth = Auth::user();
        if (Application::isAdminAreaRequest()) {
            $auth = new \stdClass();
            $auth->id = $GLOBALS["uid"];
        }
        
		global $CONFIG;
		global $_LANG;
		global $upgradeslist;
		global $orderamount;
		global $orderdescription;
		global $applytax;
		$amountToCredit = 0;
		$sessionUpgradeids['upgradeids'] = array();
		$result = \App\Models\Hosting::where('userid', $auth->id)->where('id', $id)->first();
		$data = $result->toArray();
		$packageid = $data["packageid"];
		$domain = $data["domain"];
		$nextduedate = $data["nextduedate"];
		$billingcycle = $data["billingcycle"];
		$productInfo = DB::table("tblproducts")->find($packageid, array("tax", "name", "configoptionsupgrade"));
		$applytax = $productInfo->tax;
		$allowConfigOptionsUpgrade = $productInfo->configoptionsupgrade;
		if (defined("CLIENTAREA") && !$allowConfigOptionsUpgrade) {
			// TODO: redir("type=configoptions&id=" . (int) $id, "upgrade.php");
		}
		$productname = \App\Models\Product::getProductName($packageid, $productInfo->name);
		if ($domain) {
			$productname .= " - " . $domain;
		}
		$year = substr($nextduedate, 0, 4);
		$month = substr($nextduedate, 5, 2);
		$day = substr($nextduedate, 8, 2);
		$cyclemonths = \App\Helpers\Invoice::getBillingCycleMonths($billingcycle);
		$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
		$totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
		$todaysdate = date("Ymd");
		$todaysdate = strtotime($todaysdate);
		$nextduedatetime = strtotime($nextduedate);
		$days = round(($nextduedatetime - $todaysdate) / 86400);
		if ($days < 0) {
			$days = $totaldays;
		}
		$percentage = $days / $totaldays;
		$discount = 0;
		$promoqualifies = true;
		if ($promocode) {
			$promodata = self::validateUpgradePromo($promocode);
			if (is_array($promodata)) {
				$appliesto = $promodata["appliesto"];
				$cycles = $promodata["cycles"];
				$promotype = $promodata["type"];
				$promovalue = $promodata["value"];
				$discounttype = $promodata["discounttype"];
				$upgradeconfigoptions = $promodata["configoptions"];
				$promodesc = $promodata["desc"];
				if ($promotype != "configoptions") {
					$promoqualifies = false;
				}
				if (count($appliesto) && $appliesto[0] && !in_array($packageid, $appliesto)) {
					$promoqualifies = false;
				}
				if (count($cycles) && $cycles[0] && !in_array($billingcycle, $cycles)) {
					$promoqualifies = false;
				}
				if ($discounttype == "Percentage") {
					$promovalue = $promovalue / 100;
				}
			}
			if (isset($promovalue) && $promovalue == 0) {
				$promodata = \App\Models\Promotion::where('lifetimepromo', 1)->where('recurring', 1)->where('code', $promocode)->first();
				$promodata = $promodata->toArray();
				if (is_array($promodata)) {
					if ($promodata["upgrades"] == 1) {
						$upgradeconfig = (new \App\Helpers\Client())->safe_unserialize($promodata["upgradeconfig"]);
						if ($upgradeconfig["type"] != "configoptions") {
							$promoqualifies = false;
						}
						$promovalue = $upgradeconfig["value"];
						$discounttype = $upgradeconfig["discounttype"];
						if ($discounttype == "Percentage") {
							$promovalue = $promovalue / 100;
						}
						$promoqualifies = true;
					} else {
						$promoqualifies = false;
					}
				}
			}
		}
		$configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($packageid, $configoptions, $billingcycle);
		$oldconfigoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($packageid, "", $billingcycle, $id);
		$subtotal = 0;
        $upgradearray = [];
		foreach ($configoptions as $key => $configoption) {
			$configid = $configoption["id"];
			$configname = $configoption["optionname"];
			$optiontype = $configoption["optiontype"];
			$new_selectedvalue = $configoption["selectedvalue"];
			$new_selectedqty = $configoption["selectedqty"];
			$new_selectedname = $configoption["selectedname"];
			$new_selectedsetup = $configoption["selectedsetup"];
			$new_selectedrecurring = $configoption["selectedrecurring"];
			$old_selectedvalue = $oldconfigoptions[$key]["selectedvalue"];
			$old_selectedqty = $oldconfigoptions[$key]["selectedqty"];
			$old_selectedname = $oldconfigoptions[$key]["selectedname"];
			$old_selectedsetup = $oldconfigoptions[$key]["selectedsetup"];
			$old_selectedrecurring = $oldconfigoptions[$key]["selectedrecurring"];
			if (($optiontype == 1 || $optiontype == 2) && $new_selectedvalue != $old_selectedvalue || ($optiontype == 3 || $optiontype == 4) && $new_selectedqty != $old_selectedqty) {
				$difference = $new_selectedrecurring - $old_selectedrecurring;
				$amountdue = $difference * $percentage;
				$amountdue = \App\Helpers\Functions::format_as_currency($amountdue);
				if (!$CONFIG["CreditOnDowngrade"] && $amountdue < 0) {
					$amountToCredit = $amountdue;
					$amountdue = \App\Helpers\Functions::format_as_currency(0);
				}
				if ($optiontype == 1 || $optiontype == 2) {
					$db_orig_value = $old_selectedvalue;
					$db_new_value = $new_selectedvalue;
					$originalvalue = $old_selectedname;
					$newvalue = $new_selectedname;
				} else {
					if ($optiontype == 3) {
						$db_orig_value = $old_selectedqty;
						$db_new_value = $new_selectedqty;
						if ($old_selectedqty) {
							$originalvalue = $_LANG["yes"];
							$newvalue = $_LANG["no"];
						} else {
							$originalvalue = $_LANG["no"];
							$newvalue = $_LANG["yes"];
						}
					} else {
						if ($optiontype == 4) {
							$new_selectedqty = (int) $new_selectedqty;
							if ($new_selectedqty < 0) {
								$new_selectedqty = 0;
							}
							$db_orig_value = $old_selectedqty;
							$db_new_value = $new_selectedqty;
							$originalvalue = $old_selectedqty;
							$newvalue = $new_selectedqty . " x " . $configoption["options"][0]["nameonly"];
						}
					}
				}
				$subtotal += $amountdue;
				$itemdiscount = 0;
				if ($promoqualifies && 0 < $amountdue && (!count($upgradeconfigoptions) || in_array($configid, $upgradeconfigoptions))) {
					$itemdiscount = $discounttype == "Percentage" ? round($amountdue * $promovalue, 2) : ($amountdue < $promovalue ? $amountdue : $promovalue);
				}
				$discount += $itemdiscount;
				$upgradearray[] = array("configname" => $configname, "originalvalue" => $originalvalue, "newvalue" => $newvalue, "price" => Format::formatCurrency($amountdue));
				$client = \App\Models\Client::find($auth->id);
				$totalDue = $amountdue;
				if (Cfg::get("TaxEnabled") && $applytax && $client && !$client->taxExempt) {
					$taxData = \App\Helpers\Invoice::getTaxRate(1, $client->state, $client->country);
					$taxRate = $taxData["rate"] / 100;
					$taxData = \App\Helpers\Invoice::getTaxRate(2, $client->state, $client->country);
					$taxRate2 = $taxData["rate"] / 100;
					if (Cfg::get("TaxType") == "Exclusive") {
						if (Cfg::get("TaxL2Compound")) {
							$totalDue += $totalDue * $taxRate;
							$totalDue += $totalDue * $taxRate2;
						} else {
							$totalDue += $totalDue * $taxRate + $totalDue * $taxRate2;
						}
					}
				}
				if ($checkout) {
					if ($orderdescription) {
						$orderdescription .= "<br>\n<br>\n";
					}
					$orderdescription .= $_LANG["upgradedowngradeconfigoptions"] . ": " . $configname . " - " . $originalvalue . " => " . $newvalue . "<br>\nAmount Due: " . \App\Helpers\Format::formatCurrency($totalDue);
					$paid = "N";
					if ($amountdue <= 0) {
						$paid = "Y";
					}
					$amountwithdiscount = $amountdue - $itemdiscount;
					$upgradeid = \App\Models\Upgrade::insertGetId(array("userid" => $auth->id, "type" => "configoptions", "date" => \Carbon\Carbon::now(), "relid" => $id, "originalvalue" => (string) $configid . "=>" . $db_orig_value, "newvalue" => $db_new_value, "amount" => $amountwithdiscount, "recurringchange" => $difference, "status" => "Pending", "paid" => $paid));
                    $sessionUpgradeids['upgradeids'][] = $upgradeid;
					$hookReturns = Hooks::run_hook("PreUpgradeCheckout", array("clientId" => (int) $auth->id, "upgradeId" => $upgradeid, "serviceId" => $id, "amount" => $amountdue, "discount" => $discount));
					foreach ($hookReturns as $hookReturn) {
						if (is_array($hookReturn)) {
							if (array_key_exists("amount", $hookReturn) && is_numeric($hookReturn["amount"])) {
								$amountdue = $hookReturn["amount"];
							}
							if (array_key_exists("discount", $hookReturn) && is_numeric($hookReturn["discount"])) {
								$discount = $hookReturn["discount"];
							}
							$amountwithdiscount = $amountdue - $discount;
							DB::table("tblupgrades")->where("id", $upgradeid)->update(array("amount" => $amountwithdiscount));
						}
					}
					if (0 < $amountdue) {
						\App\Models\Invoiceitem::insert(array("userid" => $auth->id, "type" => "Upgrade", "relid" => $upgradeid, "description" => $_LANG["upgradedowngradeconfigoptions"] . ": " . $productname . "\n" . $configname . ": " . $originalvalue . " => " . $newvalue . " (" . \App\Helpers\Functions::getTodaysDate() . " - " . (new \App\Helpers\Client())->fromMySQLDate($nextduedate) . ")", "amount" => $amountdue, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
						if (0 < $itemdiscount) {
							\App\Models\Invoiceitem::insert(array("userid" => $auth->id, "description" => $_LANG["orderpromotioncode"] . ": " . $promocode . " - " . $promodesc, "amount" => $itemdiscount * -1, "taxed" => $applytax, "duedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod));
						}
						$orderamount += $amountwithdiscount;
					} else {
						if ($CONFIG["CreditOnDowngrade"]) {
							$creditamount = $amountdue * -1;
							\App\Models\Credit::insert(array("clientid" => $auth->id, "date" => \Carbon\Carbon::now(), "description" => "Upgrade/Downgrade Credit", "amount" => $creditamount));
							$c = \App\Models\Client::find($auth->id);
							if ($c) {
								$c->increment('credit', $creditamount);
							}
						} else {
							if ($amountToCredit) {
								session(["UpgradeCredit" . $upgradeid => $amountToCredit]);
							}
						}
						self::doUpgrade($upgradeid);
					}
				}
			}
		}
		if (isset($upgradearray) && !count($upgradearray)) {
			if (defined("CLIENTAREA")) {
				// TODO: redir("type=configoptions&id=" . (int) $id, "upgrade.php");
			} else {
				return array();
			}
		}
		$GLOBALS["subtotal"] = $subtotal;
		$GLOBALS["qualifies"] = $promoqualifies;
		$GLOBALS["discount"] = $discount;
		session($sessionUpgradeids);
		return $upgradearray;
	}

	/**
	 * createUpgradeOrder
	 */
	public static function createUpgradeOrder($serviceId, $ordernotes, $promocode, $paymentmethod)
	{
        $auth = Auth::user();
        $authadmin = Auth::guard('admin')->user();
        if (Application::isAdminAreaRequest()) {
            $auth = new \stdClass();
            $auth->id = $GLOBALS["uid"];
        }
        
		global $CONFIG;
		global $remote_ip;
		global $orderdescription;
		global $orderamount;
		if ($promocode && !isset($GLOBALS["qualifies"])) {
			$promocode = "";
		}
		if ($promocode) {
			$result = \App\Models\Promotion::where('code', $promocode)->first();
            if ($result) {
                $data = $result->toArray();
                $upgradeconfig = @$data["upgradeconfig"];
                $upgradeconfig = (new \App\Helpers\Client())->safe_unserialize($upgradeconfig);
                $promo_type = @$upgradeconfig["discounttype"];
                $promo_value = @$upgradeconfig["value"];
                $p = \App\Models\Promotion::where('code', $promocode)->first();
                $p->increment('uses');
            }
		}
		$order_number = \App\Helpers\Functions::generateUniqueID();        
		// $orderid = \App\Models\Order::insert(array(
        //     "ordernum" => $order_number, 
        //     "userid" => $auth->id, 
        //     "date" => \Carbon\Carbon::now(), 
        //     "status" => "Pending", 
        //     "promocode" => $promocode, 
        //     "promotype" => $promo_type ?? "", 
        //     "promovalue" => $promo_value ?? "", 
        //     "paymentmethod" => $paymentmethod, 
        //     "ipaddress" => $remote_ip, 
        //     "amount" => $orderamount, 
        //     "notes" => $ordernotes
        // ));
        $newOrder = new \App\Models\Order();
        $newOrder->ordernum = $order_number;
        $newOrder->userid = $auth->id;
        $newOrder->date = \Carbon\Carbon::now();
        $newOrder->status = "Pending";
        $newOrder->promocode = $promocode;
        $newOrder->promotype = $promo_type ?? "";
        $newOrder->promovalue = $promo_value ?? "";
        $newOrder->paymentmethod = $paymentmethod;
        $newOrder->ipaddress = $remote_ip ?? "";
        $newOrder->amount = $orderamount ?? 0;
        $newOrder->notes = $ordernotes;
        $newOrder->save();

        $orderid = $newOrder->id;

		$additionalOrderNote = "";
		foreach (session("upgradeids") as $upgradeid) {
			\App\Models\Upgrade::where('id', $upgradeid)->update(array("orderid" => $orderid));
			$upgradeCreditAmount = session("UpgradeCredit" . $upgradeid);
			session()->forget("UpgradeCredit" . $upgradeid);
			if ($upgradeCreditAmount && is_numeric($upgradeCreditAmount)) {
				$additionalOrderNote .= "Upgrade Order Credit Amount Calculated as: " . \App\Helpers\Functions::format_as_currency($upgradeCreditAmount * -1) . "\r\n";
			}
		}
		if ($additionalOrderNote) {
			$ordernotes .= "\r\n==========\r\nCredit on Downgrade Disabled\r\n" . $additionalOrderNote;
			DB::table("tblorders")->where("id", $orderid)->update(array("notes" => $ordernotes));
		}
		\App\Helpers\Functions::sendMessage("Order Confirmation", $auth->id, array("order_id" => $orderid, "order_number" => $order_number, "order_details" => $orderdescription));
		LogActivity::Save("Upgrade Order Placed - Order ID: " . $orderid, $auth->id);
		$invoiceid = 0;
		$invoiceid = \App\Helpers\ProcessInvoices::createInvoices($auth->id, true);
		if ($invoiceid) {
			$result = \App\Models\Invoiceitem::where('type', 'Upgrade')->whereIn('relid', session("upgradeids"))->orderBy("invoiceid", "DESC")->first();
            if ($result) {
                $data = $result->toArray();
                $invoiceid = $data["invoiceid"];
            }
		}
		if ($CONFIG["OrderDaysGrace"]) {
			$new_time = mktime(0, 0, 0, date("m"), date("d") + $CONFIG["OrderDaysGrace"], date("Y"));
			$duedate = date("Y-m-d", $new_time);
			\App\Models\Invoice::where('id', $invoiceid)->update(array("duedate" => $duedate));
		}
		if (!$CONFIG["NoInvoiceEmailOnOrder"]) {
			if (Application::isClientAreaRequest()) {
				$source = "clientarea";
			} else {
				if (Application::isAdminAreaRequest()) {
					$source = "adminarea";
				} else {
					if (Application::isApiRequest()) {
						$source = "api";
					} else {
						$source = "autogen";
					}
				}
			}
			$invoiceArr = array("source" => $source, "user" => $authadmin ? $authadmin->id : "system", "invoiceid" => $invoiceid);
			Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
			\App\Helpers\Functions::sendMessage("Invoice Created", $invoiceid);
		}
		\App\Models\Order::where('id', $orderid)->update(array("invoiceid" => $invoiceid));
		$result = \App\Models\Client::select("firstname", "lastname", "companyname", "email", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "ip", "host")->where('id', $auth->id)->first();
        $data = [];
        if ($result) {
            $data = $result->toArray();
        }

		// list($firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $ip, $host) = $data;
		$firstname = @$data["firstname"]; 
        $lastname = @$data["lastname"]; 
        $companyname= @$data["companyname"];
        $email = @$data["email"]; 
        $address1 = @$data["address1"]; 
        $address2 = @$data["address2"];
        $city = @$data["city"]; 
        $state = @$data["state"];
        $postcode = @$data["postcode"]; 
        $country = @$data["country"]; 
        $phonenumber = @$data["phonenumber"]; 
        $ip = @$data["ip"];
        $host = @$data["host"];

		$nicegatewayname = \App\Models\Paymentgateway::where('gateway', $paymentmethod)->where('setting', 'Name')->value('value');
		$ordertotal = \App\Models\Invoice::where('id', $invoiceid)->value('total');
		$adminemailitems = "";
		if ($invoiceid) {
			$result = \App\Models\Invoiceitem::where('type', 'Upgrade')->whereIn('relid', session('upgradeids'))->orderBy("invoiceid", "DESC")->get();
            if ($result) {
                foreach ($result->toArray() as $invoicedata) {
                    $adminemailitems .= $invoicedata["description"] . "<br />";
                }
            }
		} else {
			$adminemailitems .= $orderdescription;
		}
		if (!$adminemailitems) {
			$adminemailitems = "Upgrade/Downgrade";
		}
		\App\Helpers\Functions::sendAdminMessage("New Order Notification", array("order_id" => $orderid, "order_number" => $order_number, "order_date" => date("d/m/Y H:i:s"), "invoice_id" => $invoiceid, "order_payment_method" => $nicegatewayname, "order_total" => Format::formatCurrency($ordertotal), "client_id" => $auth->id, "client_first_name" => $firstname, "client_last_name" => $lastname, "client_email" => $email, "client_company_name" => $companyname, "client_address1" => $address1, "client_address2" => $address2, "client_city" => $city, "client_state" => $state, "client_postcode" => $postcode, "client_country" => $country, "client_phonenumber" => $phonenumber, "order_items" => $adminemailitems, "order_notes" => "", "client_ip" => $ip, "client_hostname" => $host), "account");
		if (Cfg::getValue("AutoCancelSubscriptions")) {
			try {
				\App\Helpers\Gateway::cancelSubscriptionForService($serviceId, $auth->id);
			} catch (\Exception $e) {
			}
		}
		return array("id" => $serviceId, "orderid" => $orderid, "order_number" => $order_number, "invoiceid" => $invoiceid);
	}

	/**
	 * validateUpgradePromo
	 */
	public static function validateUpgradePromo($promocode)
	{
		global $_LANG;

        $auth = Auth::user();
        if (Application::isAdminAreaRequest()) {
            $auth = new \stdClass();
            $auth->id = $GLOBALS["uid"];
        }

		$result = \App\Models\Promotion::where('code', $promocode)->first();
        if (!$result) {
			return $_LANG["ordercodenotfound"];
		}
		$data = $result->toArray();
		$id = $data["id"];
		$recurringtype = $data["type"];
		$recurringvalue = $data["value"];
		$recurring = $data["recurring"];
		$cycles = $data["cycles"];
		$appliesto = $data["appliesto"];
		$requires = $data["requires"];
		$maxuses = $data["maxuses"];
		$uses = $data["uses"];
		$startdate = $data["startdate"];
		$expiredate = $data["expirationdate"];
		$existingclient = $data["existingclient"];
		$onceperclient = $data["onceperclient"];
		$upgrades = $data["upgrades"];
		$upgradeconfig = $data["upgradeconfig"];
		$upgradeconfig = (new \App\Helpers\Client())->safe_unserialize($upgradeconfig);
		$type = $upgradeconfig["discounttype"];
		$value = $upgradeconfig["value"];
		$configoptions = $upgradeconfig["configoptions"];
		if (!$id) {
			return $_LANG["ordercodenotfound"];
		}
		if (!$upgrades) {
			return $_LANG["promoappliedbutnodiscount"];
		}
		if ($startdate != "0000-00-00") {
			$startdate = str_replace("-", "", $startdate);
			if (date("Ymd") < $startdate) {
                try {
                    return $_LANG["orderpromoprestart"];
                } catch (\Throwable $th) {
                    return __("client.orderpromoprestart");
                }
			}
		}
		if ($expiredate != "0000-00-00") {
			$expiredate = str_replace("-", "", $expiredate);
			if ($expiredate < date("Ymd")) {
                try {
                    return $_LANG["orderpromoexpired"];
                } catch (\Throwable $th) {
                    return __("client.orderpromoexpired");
                }
			}
		}
		if (0 < $maxuses && $maxuses <= $uses) {
            try {
                return $_LANG["orderpromomaxusesreached"];
            } catch (\Throwable $th) {
                return __("client.orderpromomaxusesreached");
            }
		}
		if ($onceperclient) {
			$result = \App\Models\Order::where('status', 'Active')->where('userid', $auth->id)->where('promocode', $promocode)->count();
			$orderCount = $result;
			if (0 < $orderCount) {
                try {
                    return $_LANG["promoonceperclient"];
                } catch (\Throwable $th) {
                    return __("client.promoonceperclient");
                }
			}
		}
		$promodesc = $type == "Percentage" ? $value . "%" : \App\Helpers\Format::formatCurrency($value);
        try {
            $promodesc .= " " . $_LANG["orderdiscount"];
        } catch (\Throwable $th) {
            $promodesc .= " " . __("client.orderdiscount");
        }

		if (!$recurring) {
			$recurringvalue = 0;
			$recurringtype = "";
		}
		$recurringpromodesc = $recurring && 0 < $recurringvalue ? $recurringpromodesc = $recurringtype == "Percentage" ? $recurringvalue . "%" : \App\Helpers\Format::formatCurrency($recurringvalue) : "";
		$cycles = explode(",", $cycles);
		$appliesto = explode(",", $appliesto);
		$requires = explode(",", $requires);
		return array("id" => $id, "cycles" => $cycles, "appliesto" => $appliesto, "requires" => $requires, "type" => $upgradeconfig["type"], "value" => $upgradeconfig["value"], "discounttype" => $upgradeconfig["discounttype"], "configoptions" => $upgradeconfig["configoptions"], "desc" => $promodesc, "recurringvalue" => $recurringvalue, "recurringtype" => $recurringtype, "recurringdesc" => $recurringpromodesc);
	}

	/**
	 * processUpgradePayment
	 */
	public static function processUpgradePayment($upgradeid, $paidamount, $fees, $invoice = "", $gateway = "", $transid = "")
	{
		\App\Models\Upgrade::where('id', $upgradeid)->update(array("paid" => "Y"));
		self::doUpgrade($upgradeid);
	}

	/**
	 * doUpgrade
	 */
	public static function doUpgrade($upgradeid)
	{
		$newpackageid = $newbillingcycle = $billingcycle = $configid = $optiontype = "";
		$tempvalue = array();
		$upgrade = \App\Models\Upgrade::find($upgradeid);
		$orderid = $upgrade->orderId;
		$type = $upgrade->type;
		$relid = $upgrade->relid;
		$originalvalue = $upgrade->originalValue;
		$newvalue = $upgrade->newValue;
		$upgradeamount = $upgrade->upgradeAmount;
		$recurringchange = $upgrade->recurringChange;
		$result = \App\Models\Order::find($orderid);
		$data = $result ? $result->toArray() : [];
		$promocode = @$data["promocode"];
		if ($type == "package") {
			$newvalue = explode(",", $newvalue);
			list($newpackageid, $newbillingcycle) = $newvalue;
			$changevalue = "amount";
			if ($newbillingcycle == "free") {
				$newbillingcycle = "Free Account";
			} else {
				if ($newbillingcycle == "onetime") {
					$newbillingcycle = "One Time";
					$changevalue = "firstpaymentamount";
					$recurringchange = $upgradeamount;
				} else {
					if ($newbillingcycle == "monthly") {
						$newbillingcycle = "Monthly";
					} else {
						if ($newbillingcycle == "quarterly") {
							$newbillingcycle = "Quarterly";
						} else {
							if ($newbillingcycle == "semiannually") {
								$newbillingcycle = "Semi-Annually";
							} else {
								if ($newbillingcycle == "annually") {
									$newbillingcycle = "Annually";
								} else {
									if ($newbillingcycle == "biennially") {
										$newbillingcycle = "Biennially";
									} else {
										if ($newbillingcycle == "triennially") {
											$newbillingcycle = "Triennially";
										}
									}
								}
							}
						}
					}
				}
			}
			$result = \App\Models\Hosting::find($relid);
			$data = $result->toArray();
			$billingcycle = $data["billingcycle"];
			if ($billingcycle == "Free Account" || $billingcycle == "One Time") {
				$newnextdue = \App\Helpers\Invoice::getInvoicePayUntilDate(date("Y-m-d"), $newbillingcycle, true);
				\App\Models\Hosting::where('id', $relid)->update(array("nextduedate" => $newnextdue, "nextinvoicedate" => $newnextdue));
			}
			\App\Helpers\Customfield::migrateCustomFieldsBetweenProducts($relid, $newpackageid);
			$h = \App\Models\Hosting::find($relid);
			$h->packageid = $newpackageid;
			$h->billingcycle = $newbillingcycle;
			$h->increment($changevalue, $recurringchange);
			self::cancelUnpaidInvoiceForPreviousPriceAndRegenerateNewInvoiceByServiceId($relid);
			$configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($newpackageid, "", $newbillingcycle);
			foreach ($configoptions as $configoption) {
				$result = \App\Models\Hostingconfigoption::where('relid', $relid)->where('configid', $configoption["id"])->count();
				$data = $result;
				if (!$data) {
					\App\Models\Hostingconfigoption::insert(array("relid" => $relid, "configid" => $configoption["id"], "optionid" => $configoption["selectedvalue"]));
				}
			}
			$newProduct = \App\Models\Product::findOrFail($newpackageid);
            if ($newProduct) {
                if ($newProduct->stockControlEnabled) {
                    $newProduct->quantityInStock = $newProduct->quantityInStock - 1;
                    $newProduct->save();
                }
            }
			$oldProduct = \App\Models\Hosting::findOrFail($relid)->product()->first();
            if ($oldProduct) {
                if ($oldProduct->stockControlEnabled) {
                    $oldProduct->quantityInStock = $oldProduct->quantityInStock + 1;
                    $oldProduct->save();
                }
            }
			Hooks::run_hook("AfterProductUpgrade", array("upgradeid" => $upgradeid));
			Hooks::run_hook("AfterServiceUpgrade", array("upgradeId" => $upgradeid, "clientId" => $upgrade->userId, "serviceId" => $upgrade->relid));
		} else {
			if ($type == "configoptions") {
				$tempvalue = explode("=>", $originalvalue);
				$configid = $tempvalue[0];
				$result = \App\Models\Productconfigoption::find($configid);
				$data = $result->toArray();
				$optiontype = $data["optiontype"];
				$result = \App\Models\Hostingconfigoption::where('relid', $relid)->where('configid', $configid)->count();
				$data = $result;
				if (!$data) {
					\App\Models\Hostingconfigoption::insert(array("relid" => $relid, "configid" => $configid));
				}
				if ($optiontype == 1 || $optiontype == 2) {
					\App\Models\Hostingconfigoption::where('relid', $relid)->where('configid', $configid)->update(array("optionid" => $newvalue));
				} else {
					if ($optiontype == 3 || $optiontype == 4) {
						\App\Models\Hostingconfigoption::where('relid', $relid)->where('configid', $configid)->update(array("qty" => $newvalue));
					}
				}
				$h = \App\Models\Hosting::find($relid);
				$h->increment('amount', $recurringchange);
				Hooks::run_hook("AfterConfigOptionsUpgrade", array("upgradeid" => $upgradeid));
			} else {
				$newNextDueDate = \App\Helpers\Invoice::getInvoicePayUntilDate(date("Y-m-d"), $upgrade->newCycle, true);
				\App\Helpers\Customfield::migrateCustomFieldsBetweenProductsOrAddons($upgrade->relid, $upgrade->newValue, $upgrade->originalvalue, false, $upgrade->type == "addon");
				if ($upgrade->type == "service") {
					$service = \App\Models\Hosting::find($upgrade->relid);
					$service->nextDueDate = $newNextDueDate;
					$service->nextInvoiceDate = $newNextDueDate;
					$service->packageId = $upgrade->newValue;
					$service->billingCycle = $upgrade->newCycle;
					$service->recurringFee = $upgrade->newRecurringAmount;
					$service->save();
					$configoptions = getCartConfigOptions($upgrade->newValue, "", $upgrade->newCycle);
					foreach ($configoptions as $configoption) {
						$result = \App\Models\Hostingconfigoption::where('relid', $relid)->where('configid', $configoption["id"])->count();
						$data = $result;
						if (!$data) {
							\App\Models\Hostingconfigoption::insert(array("relid" => $relid, "configid" => $configoption["id"], "optionid" => $configoption["selectedvalue"]));
						}
					}
					$newProduct = $service->product();
					if ($newProduct->stockControlEnabled) {
						$newProduct->quantityInStock = $newProduct->quantityInStock - 1;
						$newProduct->save();
					}
					$oldProduct = \App\Models\Product::findOrFail($upgrade->originalValue);
					if ($oldProduct->stockControlEnabled) {
						$oldProduct->quantityInStock = $oldProduct->quantityInStock + 1;
						$oldProduct->save();
					}
				} else {
					if ($upgrade->type == "addon") {
						$addon = \App\Models\Hostingaddon::find($upgrade->relid);
						$addon->nextDueDate = $newNextDueDate;
						$addon->nextInvoiceDate = $newNextDueDate;
						$addon->addonId = $upgrade->newValue;
						$addon->billingCycle = $upgrade->newCycle;
						$addon->recurringFee = $upgrade->newRecurringAmount;
						$addon->save();
					}
				}
				self::cancelUnpaidInvoiceForPreviousPriceAndRegenerateNewInvoiceByServiceId($relid);
				if ($upgrade->type == "service") {
					Hooks::run_hook("AfterProductUpgrade", array("upgradeid" => $upgradeid));
					Hooks::run_hook("AfterServiceUpgrade", array("upgradeId" => $upgradeid, "clientId" => $upgrade->userId, "serviceId" => $upgrade->relid));
				} else {
					if ($upgrade->type == "addon") {
						Hooks::run_hook("AfterAddonUpgrade", array("upgradeid" => $upgradeid));
					}
				}
			}
		}
		if ($promocode) {
			$result = \App\Models\Promotion::select("id","type","recurring","value")->where('code', $promocode)->first();
            $data = [];
            if ($result) {
                $data = $result->toArray();
            }

            // list($promoid, $promotype, $promorecurring, $promovalue) = $data;
            $promoid = @$data["id"];
            $promotype = @$data["type"];
            $promorecurring = @$data["recurring"];
            $promovalue = @$data["value"];

			if ($promorecurring) {
				$recurringamount = \App\Helpers\ProcessInvoices::recalcRecurringProductPrice($relid);
				if ($promotype == "Percentage") {
					$discount = $recurringamount * $promovalue / 100;
					$recurringamount = $recurringamount - $discount;
				} else {
					$recurringamount = $recurringamount < $promovalue ? "0" : $recurringamount - $promovalue;
				}
				\App\Models\Hosting::where('id', $relid)->update(array("amount" => $recurringamount ?? 0, "promoid" => $promoid));
			} else {
				\App\Models\Hosting::where('id', $relid)->update(array("promoid" => "0"));
			}
		} else {
			\App\Models\Hosting::where('id', $relid)->update(array("promoid" => "0"));
		}
		if (in_array($type, array(\App\Models\Upgrade::TYPE_PACKAGE, \App\Models\Upgrade::TYPE_CONFIGOPTIONS, \App\Models\Upgrade::TYPE_SERVICE, \App\Models\Upgrade::TYPE_ADDON))) {
			if ($type === \App\Models\Upgrade::TYPE_ADDON) {
				$upgradedService = \App\Models\Hostingaddon::findOrFail($relid);
				$serverPackageId = $upgradedService->service->id;
				$serverAddonId = $upgradedService->id;
				$serverType = $upgradedService->productAddon->module;
				$upgradeEmailTemplate = NULL;
				$upgradedServiceDescription = "Addon ID: " . $relid . " - Service ID: " . $serverPackageId;
			} else {
				$upgradedService = \App\Models\Hosting::findOrFail($relid);
				$serverPackageId = $upgradedService->id;
				$serverAddonId = 0;
				$serverType = $upgradedService->product->module;
				$upgradeEmailTemplate = $upgradedService->product->upgradeEmailTemplate;
				$upgradedServiceDescription = "Service ID: " . $relid;
			}
			$userid = $upgradedService->clientId;
			$manualUpgradeRequired = false;
			if ($serverType) {
				$result = (new \App\Module\Server())->ServerChangePackage($serverPackageId, $serverAddonId);
				if ($result != "success") {
					if ($result == "Function Not Supported by Module") {
						$manualUpgradeRequired = true;
					} else {
						LogActivity::Save("Automatic Product/Service Upgrade Failed - " . $upgradedServiceDescription, $userid);
					}
				} else {
					LogActivity::Save("Automatic Product/Service Upgrade Successful - " . $upgradedServiceDescription, $userid);
					if ($upgradeEmailTemplate) {
						\App\Helpers\Functions::sendMessage($upgradeEmailTemplate, $relid);
					}
				}
			} else {
				$manualUpgradeRequired = true;
			}
			if ($manualUpgradeRequired) {
				$emailVars = array("client_id" => $userid, "service_id" => $relid, "order_id" => $orderid, "upgrade_id" => $upgradeid, "upgrade_type" => $type, "upgrade_amount" => $upgradeamount, "increase_recurring_value" => $recurringchange, "promomotion" => $promocode, "package_id" => $serverPackageId, "server_type" => $serverType);
				if ($type == "package") {
					$emailVars["new_package_id"] = $newpackageid;
					$emailVars["new_billing_cycle"] = $newbillingcycle;
					$emailVars["billing_cycle"] = $billingcycle;
				}
				if ($type == "configoptions") {
					$emailVars["config_id"] = $configid;
					$emailVars["option_type"] = $optiontype;
					$emailVars["current_value"] = $tempvalue[1];
					$emailVars["new_value"] = $newvalue;
				}
				\App\Helpers\Functions::sendAdminMessage("Manual Upgrade Required", $emailVars, "account");
				LogActivity::Save("Automatic Product/Service Upgrade not possible - " . $upgradedServiceDescription, $userid);
				DB::table("tbltodolist")->insert(array("date" => date("Y-m-d"), "title" => "Manual Upgrade Required", "description" => "Manual Upgrade Required for " . $upgradedServiceDescription, "admin" => "", "status" => "Pending", "duedate" => date("Y-m-d")));
			}
		}
		\App\Models\Upgrade::where('id', $upgradeid)->update(array("status" => "Completed"));
	}

	/**
	 * cancelUnpaidInvoiceForPreviousPriceAndRegenerateNewInvoiceByServiceId
	 */
	public static function cancelUnpaidInvoiceForPreviousPriceAndRegenerateNewInvoiceByServiceId($serviceId)
	{
		$invoiceItems = DB::table("tblinvoiceitems")->join("tblinvoices", "tblinvoices.id", "=", "tblinvoiceitems.invoiceid")->where("type", "=", "Hosting")->where("relid", "=", $serviceId)->where(DB::raw("tblinvoices.status"), "=", "Unpaid")->orderBy("invoiceid")->get(array("tblinvoiceitems.*"));
		foreach ($invoiceItems as $invoiceItem) {
			$invoiceId = $invoiceItem->invoiceid;
			$userId = $invoiceItem->userid;
			$dueDate = \Carbon\Carbon::createFromFormat("Y-m-d", $invoiceItem->duedate);
			$allInvoiceItems = DB::table("tblinvoiceitems")->where("invoiceid", "=", $invoiceId)->whereNotIn("type", array("PromoHosting", "GroupDiscount", "LateFee"))->get();
			$services = $addons = $domains = $items = array();
			foreach ($allInvoiceItems as $singleInvoiceItem) {
				switch ($singleInvoiceItem->type) {
					case "Hosting":
						$services[] = $singleInvoiceItem->relid;
						break;
					case "Addon":
						$addons[] = $singleInvoiceItem->relid;
						break;
					case "Domain":
						$domains[] = $singleInvoiceItem->relid;
						break;
					case "Item":
						$items[] = $singleInvoiceItem->relid;
						break;
				}
			}
			DB::table("tblinvoiceitems")->where("invoiceid", "=", $invoiceId)->update(array("duedate" => $dueDate->copy()->subDay()->format("Y-m-d")));
			DB::table("tblinvoices")->where("id", "=", $invoiceId)->update(array("status" => "Cancelled"));
			LogActivity::Save("Cancelled Outstanding Product Renewal Invoice - Invoice ID: " . $invoiceId . " - Service ID: " . $serviceId, $userId);
			Hooks::run_hook("InvoiceCancelled", array("invoiceid" => $invoiceId));
			if ($services) {
				DB::table("tblhosting")->whereIn("id", $services)->update(array("nextinvoicedate" => $dueDate->format("Y-m-d")));
			}
			if ($addons) {
				DB::table("tblhostingaddons")->whereIn("id", $addons)->update(array("nextinvoicedate" => $dueDate->format("Y-m-d")));
			}
			if ($domains) {
				DB::table("tbldomains")->whereIn("id", $domains)->update(array("nextinvoicedate" => $dueDate->format("Y-m-d")));
			}
			if ($items) {
				DB::table("tblbillableitems")->whereIn("id", $items)->decrement("invoicecount", 1, array("duedate" => $dueDate->format("Y-m-d")));
			}
			\App\Helpers\ProcessInvoices::createInvoices($userId);
		}
	}
}
