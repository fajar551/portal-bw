<?php

namespace App\Helpers;

// Import Controller Class here

// Import Helpers Class here
use App\Helpers\Application;
use App\Helpers\LogActivity;
use App\Helpers\Gateway;
use App\Helpers\ResponseAPI;
use App\Helpers\Cfg;
use App\Helpers\Invoice as InvoiceHelper;
use App\Helpers\Hooks;
use App\Helpers\Format;

// Import Model Class here
use App\Models\Order;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Hostingconfigoption;
use App\Models\Affiliatesaccount;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Orderstatus;
use App\Models\Customfield;
use App\Models\Customfieldsvalue;
use App\Models\Upgrade;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Account;
use App\Models\Pricing;
use App\Models\Client;
use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\Link;

// Import Package Class here
use App\Events\CancelOrder;
use App\Events\PendingOrder;
use App\Events\InvoiceCancelled;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\FacadesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class Orders
{
	private static $statusoutputs = NULL;

	public static function getAddons($pid, array $addons = [])
	{
		global $currency;
		$addonsArray = [];
		$billingCycles = [
			"monthly" => __("admin.orderpaymenttermmonthly"),
			"quarterly" => __("admin.orderpaymenttermquarterly"),
			"semiannually" => __("admin.orderpaymenttermsemiannually"),
			"annually" => __("admin.orderpaymenttermannually"),
			"biennially" => __("admin.orderpaymenttermbiennially"),
			"triennially" => __("admin.orderpaymenttermtriennially"),
		];

		$orderAddons = Addon::availableOnOrderForm($addons)->get();

		foreach ($orderAddons as $addon) {
			if (!in_array($pid, $addon->packages)) {
				continue;
			}

			$pricing = Pricing::where("type", "addon")
				->where("currency", $currency["id"])
				->where("relid", $addon->id)
				->first();

			if (!$pricing && !\App\Helpers\Cycles::isFree($addon->billingCycle)) {
				continue;
			}

			$addonPricingString = "";
			$addonBillingCycles = [];

			switch ($addon->billingCycle) {
				case "recurring":
					foreach ($billingCycles as $system => $translated) {
						$setupFeeField = substr($system, 0, 1) . "setupfee";

						if ($pricing->{$system} < 0) {
							continue;
						}

						$addonPrice = new FormatterPrice($pricing->{$system}, $currency) . " " . $translated;

						if ($pricing->{$setupFeeField} > 0) {
							$addonPrice .= " + " . new FormatterPrice($pricing->{$setupFeeField}, $currency) . " " . __("client.ordersetupfee");
						}

						if (empty($addonPricingString)) {
							$addonPricingString = $addonPrice;
						}

						$addonBillingCycles[$system] = [
							"setup" => $pricing->{$setupFeeField} > 0 ? new FormatterPrice($pricing->{$setupFeeField}, $currency) : null,
							"price" => new FormatterPrice($pricing->{$system}, $currency),
						];
					}
					break;

				case "free":
				case "Free":
				case "Free Account":
					$addonPricingString = __("admin.orderfree");
					$addonBillingCycles["free"] = ["setup" => null, "price" => null];
					break;

				case "onetime":
				case "One Time":
				default:
					$system = str_replace([" ", "-"], "", strtolower($addon->billingCycle));
					$translated = __("admin.orderpaymentterm" . $system);
					$addonPrice = new FormatterPrice($pricing->monthly, $currency) . " " . $translated;

					if ($pricing->msetupfee > 0) {
						$addonPrice .= " + " . new FormatterPrice($pricing->msetupfee, $currency) . " " . __("client.ordersetupfee");
					}

					if (empty($addonPricingString)) {
						$addonPricingString = $addonPrice;
					}

					$addonBillingCycles[$system] = [
						"setup" => new FormatterPrice($pricing->msetupfee, $currency),
						"price" => new FormatterPrice($pricing->monthly, $currency),
					];
					break;
			}

			$checkbox = "<input type=\"checkbox\" name=\"addons[{$addon->id}]\" id=\"a{$addon->id}\" class=\"form-check-input\" ";
			$status = in_array($addon->id, $addons);

			if ($status) {
				$checkbox .= "checked=\"checked\"";
			}

			$checkbox .= " />";

			$minPrice = 0;
			$minCycle = "onetime";

			foreach ($addonBillingCycles as $cycle => $price) {
				$minPrice = $price;
				$minCycle = $cycle;
				break;
			}

			$productInfoKey = request()->input('i');
			$acustomfields = session()->get("cart.products.$productInfoKey.acustomfields");
			$customfields = \App\Helpers\Customfield::getCustomFields("addon", $addon->id, "", "", "on", $acustomfields);
			$newcustomfields = [];

			foreach ($customfields as $customfield) {
				$cfid = $customfield['id'];
				$customfield['input'] = str_replace(
					["customfield[$cfid]", "id=\"customfield$cfid\""],
					["acustomfield[$cfid]", "id=\"acustomfield$cfid\""],
					$customfield['input']
				);
				$newcustomfields[] = $customfield;
			}

			$addonsArray[] = [
				"id" => $addon->id,
				"checkbox" => $checkbox,
				"name" => $addon->name,
				"description" => $addon->description,
				"pricing" => $addonPricingString,
				"billingCycles" => $addonBillingCycles,
				"minPrice" => $minPrice,
				"minCycle" => $minCycle,
				"status" => $status,
				"customfields" => $newcustomfields,
			];
		}

		return $addonsArray;
	}

	public static function ChangeOrderStatus($orderid, $status, $cancelSubscription = false)
	{
		if (!$orderid) {
			return false;
		}

		$orderid = (int) $orderid;

		// Trigger hooks based on the status
		switch ($status) {
			case Order::CANCELLED:
				\App\Helpers\Hooks::run_hook("CancelOrder", ["orderid" => $orderid]);
				break;
			case Order::REFUNDED:
				$status = Order::CANCELLED;
				break;
			case Order::FRAUD:
				\App\Helpers\Hooks::run_hook("FraudOrder", ["orderid" => $orderid]);
				break;
			case Order::PENDING:
				\App\Helpers\Hooks::run_hook("PendingOrder", ["orderid" => $orderid]);
				break;
		}

		$orderStatus = Order::where("id", $orderid)->value("status");

		$order = Order::find($orderid);
		$order->status = $status;
		$order->save();

		if (in_array($status, ["Cancelled", "Fraud"])) {
			$hostingTable = (new Hosting)->getTableName();
			$productTable = (new Product)->getTableName();

			$result = DB::table($hostingTable)
				->select(
					"{$hostingTable}.id",
					"{$hostingTable}.userid",
					"{$hostingTable}.domainstatus",
					"{$hostingTable}.packageid",
					"{$hostingTable}.paymentmethod",
					"{$productTable}.servertype",
					"{$productTable}.stockcontrol",
					"{$productTable}.qty"
				)
				->join("{$productTable}", "{$productTable}.id", "=", "{$hostingTable}.packageid")
				->where("orderid", $orderid);

			foreach ($result->get() as $data) {
				$userId = $data->userid;

				if ($cancelSubscription) {
					try {
						Gateway::CancelSubscriptionForService($data->id, $userId);
					} catch (\Exception $e) {
						Order::where("id", $orderid)->update(["status" => $orderStatus]);
						return "subcancelfailed";
					}
				}

				$productid = $data->id;

				$hostingAddonTable = (new Hostingaddon)->getTableName();
				$addonTable = (new Addon)->getTableName();

				$addons = DB::table($hostingAddonTable)
					->where("hostingid", $productid)
					->where("status", "!=", $status)
					->leftJoin("{$addonTable}", "{$addonTable}.id", "=", "{$hostingAddonTable}.addonid")
					->get(["{$hostingAddonTable}.id", "userid", "status", "module"]);

				$cancelResult = self::ProcessAddonsCancelOrFraud($addons, $status);

				if (Application::isApiRequest() && is_array($cancelResult)) {
					return $cancelResult;
				}

				$prodstatus = $data->domainstatus;
				$module = $data->servertype;
				$packageid = $data->packageid;
				$stockcontrol = $data->stockcontrol;

				if ($module && in_array($prodstatus, ["Active", "Suspended"])) {
					LogActivity::Save("Running Module Terminate on Order Cancel", $userId);
					$serverModule = \Module::find($module);

					if ($serverModule) {
						$server = new \App\Module\Server();
						$moduleResult = $server->ServerTerminateAccount($productid);

						if ($moduleResult === "success") {
							Hosting::where('id', $productid)->update(['domainstatus' => $status]);
							if ($stockcontrol) {
								Product::find($packageid)->increment('qty');
							}
						}
					} else {
						$errMsg = "Invalid Server Module Name";
						if (Application::isApiRequest()) {
							return ResponseAPI::Error(['message' => $errMsg]);
						}
						throw new \App\Exceptions\Fatal($errMsg);
					}
				} else {
					Hosting::where('id', $productid)->update(['domainstatus' => $status]);
					if ($stockcontrol) {
						Product::find($packageid)->increment('qty');
					}
				}
			}

			$addons = DB::table($hostingAddonTable)
				->where("orderid", $orderid)
				->where("status", "!=", $status)
				->leftJoin("{$addonTable}", "{$addonTable}.id", "=", "{$hostingAddonTable}.addonid")
				->get(["{$hostingAddonTable}.id", "userid", "status", "module"]);

			$cancelResult = self::ProcessAddonsCancelOrFraud($addons, $status);

			if (Application::isApiRequest() && is_array($cancelResult)) {
				return $cancelResult;
			}
		} else {
			Hosting::where('orderid', $orderid)->update(['domainstatus' => $status]);
			Hostingaddon::where('orderid', $orderid)->update(['status' => $status]);
		}

		if ($status == "Pending") {
			$domains = Domain::where('orderid', $orderid)->get();
			foreach ($domains as $domain) {
				$domainStatus = $domain->type === "Transfer" ? "Pending Transfer" : "Pending";
				Domain::where('id', $domain->id)->update(['status' => $domainStatus]);
			}
		} else {
			Domain::where('orderid', $orderid)->update(['status' => $status]);
		}

		$userid = $order->userid;
		$invoiceid = $order->invoiceid;

		if ($invoiceid) {
			if ($status == "Pending") {
				Invoice::where('id', $invoiceid)
					->where('status', 'Cancelled')
					->update(['status' => 'Unpaid']);
			} else {
				Invoice::where('id', $invoiceid)
					->where('status', 'Unpaid')
					->update(['status' => 'Cancelled']);
				\App\Helpers\Hooks::run_hook("InvoiceCancelled", ["invoiceid" => $invoiceid]);
				InvoiceHelper::RefundCreditOnStatusChange($invoiceid, $status);
			}
		}

		LogActivity::Save("Order Status set to {$status} - Order ID: {$orderid}", $userid ?? 0);
	}

	public static function ProcessAddonsCancelOrFraud($addonCollection, $status)
	{
		foreach ($addonCollection as $addon) {
			$addonId = $addon->id;
			$module = $addon->module;
			$addonStatus = $addon->status;
			if ($module && in_array($addonStatus, array("Active", "Suspended"))) {
				LogActivity::Save("Running Module Terminate on Order Cancel - Addon ID: " . $addonId, $addon->userid);
				$server = new \App\Module\Server();
				if (!$server->loadByAddonId($addonId)) {
					$errMsg = "Invalid Server Module Name";
					if (Application::isApiRequest()) {
						return ResponseAPI::Error([
							'message' => $errMsg,
						]);
					}
					throw new \App\Exceptions\Fatal($errMsg);
				}

				$moduleResult = $server->call("Terminate");
				if ($moduleResult == "success") {
					Hostingaddon::where("id", "=", $addonId)->update(array("status" => $status));
				}
			} else {
				Hostingaddon::where("id", "=", $addonId)->update(array("status" => $status));
			}
		}
		return "";
	}

	public static function CanOrderBeDeleted($orderID, $orderStatus = "")
	{
		if (!$orderID) {
			return false;
		}
		static $cancelledStatuses = NULL;
		if (!is_array($cancelledStatuses)) {
			$cancelledStatuses = Orderstatus::where("showcancelled", 1)->pluck("title")->toArray();
		}
		$orderID = (int) $orderID;
		if (!$orderStatus) {
			$orderDetails = Order::find($orderID, array("tblorders.status as orderStatus"));
			if ($orderDetails) {
				$orderStatus = $orderDetails->orderStatus;
			} else {
				return false;
			}
		}
		if (in_array($orderStatus, $cancelledStatuses) || $orderStatus == "Fraud") {
			return true;
		}
		return false;
	}

	public static function DeleteOrder($orderid)
	{
		if (!$orderid) {
			return false;
		}
		$orderid = (int) $orderid;
		\App\Helpers\Hooks::run_hook("DeleteOrder", array("orderid" => $orderid));

		$data = Order::select('userid', 'invoiceid')->where('id', $orderid)->first();
		if (!self::CanOrderBeDeleted($orderid)) {
			return false;
		}

		$userid = $data->userid;
		$invoiceid = $data->invoiceid;

		$relids = Hosting::where('orderid', $orderid)->pluck('id')->toArray();
		Hostingconfigoption::whereIn('relid', $relids)->delete();
		Affiliatesaccount::whereIn('relid', $relids)->delete();

		$hostingTable = (new Hosting)->getTableName();
		$customfiledTable = (new Customfield)->getTableName();
		$data = DB::table($hostingTable)
			->select("{$hostingTable}.id AS relid", "{$customfiledTable}.id AS fieldid")
			->join($customfiledTable, "{$customfiledTable}.relid", "=", "{$hostingTable}.packageid")
			->where("{$hostingTable}.orderid", $orderid)
			->where("{$customfiledTable}.type", "product")
			->get();
		foreach ($data as $key => $value) {
			$hostingid = $value->relid;
			$customfieldid = $value->fieldid;
			Customfieldsvalue::where('relid', $hostingid)->where('fieldid', $customfieldid)->delete();
		}

		Hosting::where('orderid', $orderid)->delete();
		foreach (Hostingaddon::where("orderid", $orderid)->get() as $serviceAddon) {
			$serviceAddon->delete();
		}

		Domain::where('orderid', $orderid)->delete();
		Upgrade::where('orderid', $orderid)->delete();
		Order::where('id', $orderid)->delete();
		Invoice::where('id', $invoiceid)->delete();
		Invoiceitem::where('invoiceid', $invoiceid)->delete();

		LogActivity::Save("Deleted Order - Order ID: " . $orderid, $userid);
	}

	public static function AcceptOrder($orderid, $vars = [])
	{
		if (!$orderid) {
			return false;
		}

		if (!is_array($vars)) {
			$vars = [];
		}

		$errors = [];
		\App\Helpers\Hooks::run_hook("AcceptOrder", ["orderid" => $orderid]);

		$hostingResults = Hosting::where('orderid', $orderid)
			->where('domainstatus', 'Pending')
			->get();

		foreach ($hostingResults as $data) {
			$productid = $data->id;
			$userId = $data->userid;
			$hosting = Hosting::find($productid);

			$hosting->server = $vars["products"][$productid]["server"] ?? $vars["api"]["serverid"] ?? $hosting->server;
			$hosting->username = $vars["products"][$productid]["username"] ?? $vars["api"]["username"] ?? $hosting->username;
			$hosting->password = isset($vars["products"][$productid]["password"]) || isset($vars["api"]["password"])
				? (new \App\Helpers\Pwd())->encrypt($vars["products"][$productid]["password"] ?? $vars["api"]["password"])
				: $hosting->password;

			$hosting->save();

			$hostingTable = (new Hosting)->getTableName();
			$productTable = (new Product)->getTableName();
			$moduleData = DB::table($hostingTable)
				->join($productTable, "{$productTable}.id", "=", "{$hostingTable}.packageid")
				->select("{$productTable}.servertype", "{$productTable}.autosetup")
				->where("{$hostingTable}.id", $productid)
				->first();

			$module = $moduleData->servertype ?? "";
			$autosetup = $moduleData->autosetup ?? false;
			$sendwelcome = $autosetup;

			if (!empty($vars)) {
				$autosetup = $vars["products"][$productid]["runcreate"] ?? $vars["api"]["autosetup"] ?? $autosetup;
				$sendwelcome = $vars["products"][$productid]["sendwelcome"] ?? $vars["api"]["sendemail"] ?? $sendwelcome;
			}

			if ($autosetup) {
				if ($module) {
					LogActivity::Save("Running Module Create on Accept Pending Order", $userId);
					$moduleInstance = \Module::find($module);

					if ($moduleInstance) {
						$server = new \App\Module\Server();
						$moduleResult = $server->ServerCreateAccount($productid);

						if ($moduleResult === "success") {
							if ($sendwelcome && $module !== "marketconnect") {
								\App\Helpers\Functions::sendMessage("defaultnewacc", $productid);
							}
						} else {
							$errors[] = $moduleResult;
						}
					} else {
						$errorMessage = "Invalid Server Module Name";
						if (Application::isApiRequest()) {
							return ResponseAPI::Error(['message' => $errorMessage]);
						}
						throw new \App\Exceptions\Fatal($errorMessage);
					}
				}
			} else {
				$hosting->domainstatus = "Active";
				$hosting->save();

				if ($sendwelcome) {
					\App\Helpers\Functions::sendMessage("defaultnewacc", $productid);
				}
			}
		}

		// Handle Addons
		$addons = Hostingaddon::with("productAddon")
			->where("orderid", $orderid)
			->where("status", "Pending")
			->get();

		foreach ($addons as $addon) {
			$addonId = $addon->id;
			$autoSetup = $addon->productAddon->autoActivate;
			$sendWelcomeEmail = $autoSetup && $addon->productAddon->welcomeEmailTemplateId;

			// Override variables
			if (!empty($vars)) {
				$autoSetup = $vars["addons"][$addonId]["runcreate"] ?? $vars["api"]["autosetup"] ?? $autoSetup;
				$sendWelcomeEmail = $vars["addons"][$addonId]["sendwelcome"] ?? $vars["api"]["sendemail"] ?? $sendWelcomeEmail;
			}

			if ($sendWelcomeEmail && !$addon->productAddon->welcomeEmailTemplateId) {
				$sendWelcomeEmail = false;
			}

			if ($autoSetup) {
				if ($addon->productAddon->module) {
					$automation = \App\Helpers\AddonAutomation::factory($addon);
					$automationResult = $automation->runAction("CreateAccount");

					if ($addon->productAddon->module === "marketconnect") {
						$sendWelcomeEmail = false;
					}

					if (!$automationResult) {
						$errors[] = "Addon automation failed for ID: {$addonId}";
					}
				}

				if ($sendWelcomeEmail) {
					\App\Helpers\Functions::sendMessage(
						$addon->productAddon->welcomeEmailTemplate,
						$addon->serviceId,
						[
							"addon_order_id" => $orderid,
							"addon_id" => $addonId,
						]
					);
				}

				$addon->status = "Active";
				$addon->save();
			} else {
				if ($sendWelcomeEmail) {
					\App\Helpers\Functions::sendMessage(
						$addon->productAddon->welcomeEmailTemplate,
						$addon->serviceId,
						[
							"addon_order_id" => $orderid,
							"addon_id" => $addonId,
						]
					);
				}

				$addon->status = "Active";
				$addon->save();
			}
		}

		// Handle Domains
		$domains = Domain::where('orderid', $orderid)
			->where('status', 'Pending')
			->get();

		foreach ($domains as $domainData) {
			$domainId = $domainData->id;
			$registrar = $vars["domains"][$domainId]["registrar"] ?? $vars["api"]["registrar"] ?? $domainData->registrar;

			if ($registrar) {
				$domainData->registrar = $registrar;
				$domainData->save();
			}

			if (!empty($vars["domains"][$domainId]["sendregistrar"]) && $registrar) {
				$module = new \App\Module\Registrar();
				$moduleResult = $domainData->type === "Transfer"
					? $module->RegTransferDomain(["domainid" => $domainId])
					: $module->RegRegisterDomain(["domainid" => $domainId]);

				if (!isset($moduleResult["error"])) {
					if (!empty($vars["domains"][$domainId]["sendemail"])) {
						\App\Helpers\Functions::sendMessage("Domain Registration Confirmation", $domainId);
					}
				} else {
					$errors[] = $moduleResult["error"];
				}
			} else {
				$domainData->status = "Active";
				$domainData->save();

				if (!empty($vars["domains"][$domainId]["sendemail"])) {
					\App\Helpers\Functions::sendMessage("Domain Registration Confirmation", $domainId);
				}
			}
		}

		// Update Order Status
		if (empty($errors)) {
			$order = Order::find($orderid);
			$order->status = "Active";
			$order->save();
			LogActivity::Save("Order Accepted - Order ID: {$orderid}", $order->userid);
		}

		return $errors;
	}

	public static function calcCartTotals($checkout = false, $ignorenoconfig = false, array $currency = [])
	{
		global $CONFIG;
		global $_LANG;
		global $remote_ip;
		global $promo_data;

		$promo_data = [
			"id" => "",
			"code" => "",
			"type" => "",
			"recurring" => 0,
			"value" => 0,
			"cycles" => "",
			"appliesto" => "",
			"requires" => "",
			"requiresexisting" => 0,
			"startdate" => "",
			"expirationdate" => "0000-00-00",
			"maxuses" => 0,
			"uses" => 0,
			"lifetimepromo" => 0,
			"applyonce" => 0,
			"newsignups" => 0,
			"existingclient" => 0,
			"onceperclient" => 0,
			"recurfor" => 0,
			"upgrades" => 0,
			"upgradeconfig" => "",
			"notes" => "",
			"promoapplied" => false,
		];
		if (!$remote_ip) {
			$remote_ip = Request::ip();
		}
		$auth = Auth::guard("web")->user();
		$isAdmin = false;
		if (defined("ADMINAREA") || defined("APICALL") || Application::isAdminAreaRequest() || Application::isApiRequest()) {
			$isAdmin = true;
		}
		if (!$currency) {
			$userId = $auth ? $auth->id : 0;
			$currencyId = session("currency");
			$currency = \App\Helpers\Format::getCurrency($userId, $currencyId);
		}

		$orderForm = new \App\Helpers\OrderForm();
		$cart_total = $cart_discount = 0;
		$cart_tax = [];
		$recurring_tax = [];

		\App\Helpers\Hooks::run_hook("PreCalculateCartTotals", $orderForm->getCartData());
		if (!$ignorenoconfig) {
			if ($orderForm->getCartDataByKey("products")) {
				foreach ($orderForm->getCartDataByKey("products") as $key => $productdata) {
					if (isset($productdata["noconfig"]) && $productdata["noconfig"]) {
						session()->forget("cart.products.$key");
					}
				}
			}
			$bundlewarnings = \App\Helpers\Cart::bundlesValidateCheckout();
			if ($orderForm->getCartDataByKey("products")) {
				session()->put('cart.products', array_values(session('cart.products')));
			}
		}

		// CHECKOUT: Step 2
		if ($checkout) {
			if (!session("cart")) {
				return false;
			}
			\App\Helpers\Hooks::run_hook("PreShoppingCartCheckout", session("cart") ?? []);
			$ordernumhooks = \App\Helpers\Hooks::run_hook("OverrideOrderNumberGeneration", session("cart") ?? []);
			$order_number = "";
			if (count($ordernumhooks)) {
				foreach ($ordernumhooks as $ordernumhookval) {
					if (is_numeric($ordernumhookval)) {
						$order_number = $ordernumhookval;
					}
				}
			}
			if (!$order_number) {
				$order_number = \App\Helpers\Functions::generateUniqueID();
			}
			$paymentmethod = session("cart.paymentmethod");
			if (Auth::guard('admin')->check()) {
				$gateways = new \App\Module\Gateway();
				if (!$gateways->isActiveGateway($paymentmethod)) {
					$paymentmethod = $gateways->getFirstAvailableGateway();
				}
			} else {
				$availablegateways = self::getAvailableOrderPaymentGateways();
				if (!array_key_exists($paymentmethod, $availablegateways)) {
					foreach ($availablegateways as $k => $v) {
						$paymentmethod = $k;
						break;
					}
				}
			}
			$userid = $auth ? $auth->id : 0;
			$ordernotes = "";
			if (session("cart.notes") && session("cart.notes") != $_LANG["ordernotesdescription"]) {
				$ordernotes = session("cart.notes");
			}
			if ($orderForm->getNumItemsInCart() <= 0) {
				return false;
			}
			$orderid = \App\Models\Order::insertGetId(array("ordernum" => $order_number, "userid" => $userid, "contactid" => session("cart.contact") ?? 0, "date" => \Carbon\Carbon::now(), "status" => "Pending", "paymentmethod" => $paymentmethod, "ipaddress" => $remote_ip, "notes" => $ordernotes));
			LogActivity::Save("New Order Placed - Order ID: " . $orderid . " - User ID: " . $userid);
			$domaineppcodes = [];
		}
		
		$promotioncode = $orderForm->getCartDataByKey("promo");
		if ($promotioncode) {
			$result = \App\Models\Promotion::where('code', $promotioncode)->first();
			if ($result) {
				$promo_data = $result->toArray();
			}
		}

		if (!$auth) {
			if (!session("cart.user.country")) {
				session()->put('cart.user.country', $CONFIG["DefaultCountry"]);
			}
			$state = session()->get('cart.user.state', '');
			$country = session()->get('cart.user.country', '');
		} else {
			$clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($auth ? $auth->id : 0);
			$state = $clientsdetails["state"];
			$country = $clientsdetails["country"];
		}

		if (isset($clientsdetails["taxexempt"])) {
			$clientsdetails["taxexempt"] = $clientsdetails["taxexempt"];
		} else {
			$clientsdetails["taxexempt"] = false;
		}

		$taxCalculator = new \App\Helpers\Tax();
		$taxCalculator->setIsInclusive($CONFIG["TaxType"] == "Inclusive")->setIsCompound($CONFIG["TaxL2Compound"]);

		if ($CONFIG["TaxEnabled"]) {
			$taxdata = \App\Helpers\Invoice::getTaxRate(1, $state, $country);
			$taxname = $taxdata["name"];
			$taxrate = $taxdata["rate"];
			$rawtaxrate = $taxrate;
			$inctaxrate = $taxrate / 100 + 1;
			$taxrate /= 100;
			$taxCalculator->setLevel1Percentage($taxdata["rate"]);

			$taxdata = \App\Helpers\Invoice::getTaxRate(2, $state, $country);
			$taxname2 = $taxdata["name"];
			$taxrate2 = $taxdata["rate"];
			$rawtaxrate2 = $taxrate2;
			$inctaxrate2 = $taxrate2 / 100 + 1;
			$taxrate2 /= 100;
			$taxCalculator->setLevel2Percentage($taxdata["rate"]);
		}

		if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct") && Cfg::getValue("TaxType") == "Inclusive" && (!$taxrate && !$taxrate2 || (isset($clientsdetails["taxexempt"]) && $clientsdetails["taxexempt"]))) {
			$systemFirstTaxRate = DB::table("tbltax")->value("taxrate");
			$excltaxrate = $systemFirstTaxRate ? 1 + $systemFirstTaxRate / 100 : 1;
		} else {
			$excltaxrate = 1;
		}

		$cartdata = $productsarray = $tempdomains = $orderproductids = $orderdomainids = $orderaddonids = $orderrenewalids = $freedomains = [];
		$recurring_cycles_total = ["monthly" => 0, "quarterly" => 0, "semiannually" => 0, "annually" => 0, "biennially" => 0, "triennially" => 0, "free" => 0, "freeaccount" => 0, "onetime" => 0];
		$cartProducts = $orderForm->getCartDataByKey("products");

		if (is_array($cartProducts)) {
			$productRemovedFromCart = false;
			$one_time_discount_applied = false;

			foreach ($cartProducts as $key => $productdata) {
				$data = \App\Models\Product::selectRaw("tblproducts.*, tblproductgroups.name AS groupname")
					->where("tblproducts.id", $productdata["pid"])
					->join("tblproductgroups", "tblproductgroups.id", "=", "tblproducts.gid")
					->first()
					->toArray();

				$pid = $data["id"];
				$gid = $data["gid"];
				$groupname = $isAdmin && !$checkout ? $data["groupname"] : \App\Models\Productgroup::getGroupName($gid, $data["groupname"]);
				$productname = $isAdmin && !$checkout ? $data["name"] : \App\Models\Product::getProductName($pid, $data["name"]);
				$paytype = $data["paytype"];
				$allowqty = $data["allowqty"];
				$proratabilling = $data["proratabilling"];
				$proratadate = $data["proratadate"];
				$proratachargenextmonth = $data["proratachargenextmonth"];
				$tax = $data["tax"];
				$servertype = $data["servertype"];
				$servergroup = $data["servergroup"];
				$stockcontrol = $data["stockcontrol"];
				$qty = isset($productdata["qty"]) ? $productdata["qty"] : 1;

				if (!$allowqty || !$qty) {
					$qty = 1;
				}

				$productdata["allowqty"] = $allowqty;

				if ($stockcontrol) {
					$quantityAvailable = (int) $data["qty"];
					if (!defined("ADMINAREA") || !Application::isAdminAreaRequest()) {
						if ($quantityAvailable <= 0) {
							session()->forget("cart.products.$key");
							$productRemovedFromCart = true;
							continue;
						}
						if ($quantityAvailable < $qty) {
							$qty = $quantityAvailable;
						}
					}
				}

				$productdata["qty"] = $qty;
				$freedomain = $data["freedomain"];

				if ($freedomain) {
					$freedomainpaymentterms = explode(",", $data["freedomainpaymentterms"]);
					$freedomaintlds = explode(",", $data["freedomaintlds"]);
				} else {
					$freedomainpaymentterms = $freedomaintlds = [];
				}

				$productinfo = self::getproductinfo($pid);

				if (array_key_exists("sslCompetitiveUpgrade", $productdata) && $productdata["sslCompetitiveUpgrade"]) {
					$productinfo["name"] .= "<br><small>" . Lang::get("store.ssl.competitiveUpgradeQualified") . "</small>";
				}

				$productdata["productinfo"] = $productinfo;
				$customfields = \App\Helpers\Customfield::getCustomFields("product", $pid, "", $isAdmin, "", $productdata["customfields"]);
				$productdata["customfields"] = $customfields;

				// NEWFEATURE: customfield addons
				$productdata["acustomfieldsoriginal"] = $productdata["acustomfields"] ?? [];
				$acustomfieldsarray = [];
				$addons = $productdata["addons"];

				if ($addons) {
					foreach ($addons as $addonid) {
						$acustomfields = \App\Helpers\Customfield::getCustomFields("addon", $addonid, "", $isAdmin, "", $productdata["acustomfields"] ?? []);
						$acustomfieldsarray[$addonid] = $acustomfields;
					}
				}

				$acustomfieldsarrayClear = collect($acustomfieldsarray)->collapse()->all();
				$productdata["acustomfields"] = $acustomfieldsarrayClear;
				$productdata["acustomfieldsformatted"] = $acustomfieldsarray;
				$pricing = self::getpricinginfo($pid);

				if ($paytype != "free") {
					$prod = new \App\Helpers\Pricing();
					$prod->loadPricing("product", $pid);
					if (!$prod->hasBillingCyclesAvailable()) {
						session()->forget("cart.products.$key");
						continue;
					}
				}

				if ($pricing["type"] == "recurring") {
					$billingcycle = strtolower($productdata["billingcycle"]);
					$validCycles = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];

					if (!in_array($billingcycle, $validCycles) || (isset($pricing["rawpricing"][$billingcycle]) && $pricing["rawpricing"][$billingcycle] < 0)) {
						$billingcycle = "";
					}

					if (!$billingcycle) {
						foreach ($validCycles as $cycle) {
							if (isset($pricing["rawpricing"][$cycle]) && $pricing["rawpricing"][$cycle] >= 0) {
								$billingcycle = $cycle;
								break;
							}
						}
					}
				} else if ($pricing["type"] == "onetime") {
					$billingcycle = "onetime";
				} else {
					$billingcycle = "free";
				}

				Log::debug(['$billingcycle', $billingcycle]);

				$productdata["billingcycle"] = $billingcycle;
				$productdata["billingcyclefriendly"] = Lang::get("client.orderpaymentterm" . $billingcycle);

				if ($billingcycle == "free") {
					$product_setup = $product_onetime = $product_recurring = "0";
					$databasecycle = "Free Account";
				} elseif ($billingcycle == "onetime") {
					$product_setup = $pricing["rawpricing"]["msetupfee"];
					$product_onetime = $pricing["rawpricing"]["monthly"];
					$product_recurring = 0;
					$databasecycle = "One Time";
				} else {
					$setupFeeKey = substr($billingcycle, 0, 1) . "setupfee";
					$product_setup = $pricing["rawpricing"][$setupFeeKey] ?? 0;
					$product_onetime = $product_recurring = $pricing["rawpricing"][$billingcycle] ?? 0;
					$databasecycle = ucfirst($billingcycle);

					if ($databasecycle === "Semiannually") {
						$databasecycle = "Semi-Annually";
					}
				}

				if ($product_setup < 0) {
					$product_setup = 0;
				}

				$before_priceoverride_value = "";

				if ($bundleoverride = \App\Helpers\Cart::bundlesGetProductPriceOverride("product", $key)) {
					$before_priceoverride_value = $product_setup + $product_onetime;
					$product_setup = 0;
					$product_onetime = $product_recurring = $bundleoverride;
				}

				$hookret = \App\Helpers\Hooks::run_hook("OrderProductPricingOverride", ["key" => $key, "pid" => $pid, "proddata" => $productdata]);

				foreach ($hookret as $hookret2) {
					if (is_array($hookret2)) {
						if ($hookret2["setup"]) {
							$product_setup = $hookret2["setup"];
						}
						if ($hookret2["recurring"]) {
							$product_onetime = $product_recurring = $hookret2["recurring"];
						}
					}
				}

				$productdata["pricing"]["baseprice"] = new \App\Helpers\FormatterPrice($product_onetime, $currency);
				$configoptionsdb = [];
				$configurableoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, $productdata["configoptions"], $billingcycle, "", "", true);
				$configoptions = [];

				if ($configurableoptions) {
					foreach ($configurableoptions as $confkey => $value) {
						if (!$value["hidden"] || defined("ADMINAREA") || defined("APICALL") || Application::isAdminAreaRequest() || Application::isApiRequest()) {
							$configoptions[] = [
								"name" => $value["optionname"],
								"type" => $value["optiontype"],
								"option" => $value["selectedoption"],
								"optionname" => $value["selectedname"],
								"setup" => 0 < $value["selectedsetup"] ? new \App\Helpers\FormatterPrice($value["selectedsetup"], $currency) : "",
								"recurring" => new \App\Helpers\FormatterPrice($value["selectedrecurring"], $currency),
								"qty" => $value["selectedqty"]
							];
							$product_setup += $value["selectedsetup"];
							$product_onetime += $value["selectedrecurring"];
							if (strlen($before_priceoverride_value)) {
								$before_priceoverride_value += $value["selectedrecurring"];
							}
							if ($billingcycle != "onetime") {
								$product_recurring += $value["selectedrecurring"];
							}
						}
						$configoptionsdb[$value["id"]] = ["value" => $value["selectedvalue"], "qty" => $value["selectedqty"]];
					}
				}

				$productdata["configoptions"] = $configoptions;

				if (in_array($billingcycle, $freedomainpaymentterms)) {
					$domain = $productdata["domain"];
					$domainparts = explode(".", $domain, 2);
					$tld = "";

					if (array_key_exists(1, $domainparts)) {
						$tld = "." . $domainparts[1];
					}

					if (in_array($tld, $freedomaintlds)) {
						$freedomains[$domain] = $freedomain;
					}
				}

				if ($proratabilling) {
					$proratavalues = \App\Helpers\Invoice::getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, date("d"), date("m"), date("Y"), $auth ? $auth->id : 0);
					$product_onetime = $proratavalues["amount"];
					$productdata["proratadate"] = (new \App\Helpers\Functions())->fromMySQLDate($proratavalues["date"]);
				}

				if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct")) {
					$product_setup = \App\Helpers\Functions::format_as_currency($product_setup / $excltaxrate);
					$product_onetime = \App\Helpers\Functions::format_as_currency($product_onetime / $excltaxrate);
					$product_recurring = \App\Helpers\Functions::format_as_currency($product_recurring / $excltaxrate);
				}

				$product_total_today_db = $product_setup + $product_onetime;
				$product_recurring_db = $product_recurring;
				Log::debug(['[0]$product_recurring_db', $product_recurring_db]);

				$productdata["pricing"]["setup"] = $product_setup * $qty;
				$productdata["pricing"]["recurring"][$billingcycle] = $product_recurring * $qty;
				$productdata["pricing"]["totaltoday"] = $product_total_today_db * $qty;
				$productdata["pricing"]["productonlysetup"] = $productdata["pricing"]["setup"];
				$productdata["pricing"]["totaltodayexcltax"] = $productdata["pricing"]["totaltoday"];
				$productdata["pricing"]["totalTodayExcludingTaxSetup"] = $product_onetime * $qty;
				$productdata["pricing"]["addons"] = 0;

				if ($product_onetime == 0 && $product_recurring == 0) {
					$pricing_text = $_LANG["orderfree"];
				} else {
					$pricing_text = "";
					if (strlen($before_priceoverride_value)) {
						$pricing_text .= "<strike>" . new \App\Helpers\FormatterPrice($before_priceoverride_value, $currency) . "</strike> ";
					}
					$pricing_text .= new \App\Helpers\FormatterPrice($product_onetime, $currency);
					if (0 < $product_setup) {
						$pricing_text .= " + " . new \App\Helpers\FormatterPrice($product_setup, $currency) . " " . $_LANG["ordersetupfee"];
					}
					if ($allowqty && 1 < $qty) {
						$pricing_text .= $_LANG["invoiceqtyeach"] . "<br />" . $_LANG["invoicestotal"] . ": " . new \App\Helpers\FormatterPrice($productdata["pricing"]["totaltoday"], $currency);
					}
				}

				$productdata["pricingtext"] = $pricing_text;

				if (isset($productdata["priceoverride"])) {
					$product_total_today_db = $product_recurring_db = $product_onetime = $productdata["priceoverride"];
					Log::debug(['[1]$product_recurring_db', $product_recurring_db]);
					$product_setup = 0;
				}

				$applyTaxToCart = $CONFIG["TaxEnabled"] && $tax && (isset($clientsdetails["taxexempt"]) && !$clientsdetails["taxexempt"]);

				if ($applyTaxToCart) {
					$cart_tax = array_merge($cart_tax, array_fill(0, $qty, $product_total_today_db));
					if (!isset($recurring_tax[$billingcycle])) {
						$recurring_tax[$billingcycle] = [];
					}
					$recurring_tax[$billingcycle] = array_merge($recurring_tax[$billingcycle], array_fill(0, $qty, $product_recurring_db));
					Log::debug(['[2]$product_recurring_db', $product_recurring_db]);
				}

				$firstqtydiscountonly = false;
				if ($promotioncode) {
					$onetimediscount = $recurringdiscount = $promoid = $firstqtydiscountedamtonetime = $firstqtydiscountedamtrecurring = 0;
					if ($promocalc = self::CalcPromoDiscount($pid, $databasecycle, $product_total_today_db, $product_recurring_db, $product_setup)) {
						$onetimediscount = $promocalc["onetimediscount"];
						$recurringdiscount = $promocalc["recurringdiscount"];
						$product_total_today_db -= $onetimediscount;
						$product_recurring_db -= $recurringdiscount;
						if (1 < $qty) {
							$applyonce = $promocalc["applyonce"];
							if ($applyonce) {
								$cart_discount += $onetimediscount;
								$firstqtydiscountonly = true;
								$firstqtydiscountedamtonetime = $product_total_today_db;
								$firstqtydiscountedamtrecurring = $product_recurring_db;
								$product_total_today_db += $onetimediscount;
								$product_recurring_db += $recurringdiscount;
							} else {
								$cart_discount += $onetimediscount * $qty;
							}
						} else {
							$cart_discount += $onetimediscount;
						}
						if ($applyTaxToCart) {
							$discount_quantity = $firstqtydiscountonly ? 1 : $qty;
							if ($onetimediscount != 0) {
								$cart_tax = array_merge($cart_tax, array_fill(0, $discount_quantity, 0 - $onetimediscount));
							}
							if ($recurringdiscount != 0) {
								$recurring_tax[$billingcycle] = array_merge($recurring_tax[$billingcycle], array_fill(0, $discount_quantity, 0 - $recurringdiscount));
							}
						}
						$promoid = $promo_data["id"];
					}
				}

				$cart_total += $product_total_today_db * $qty;
				Log::debug(['[2] $cart_total += $product_total_today_db * $qty;', $cart_total, $product_total_today_db, $qty]);

				$product_total_qty_recurring = $product_recurring_db * $qty;
				Log::debug(['[1]$product_total_qty_recurring', $product_total_qty_recurring]);

				if ($firstqtydiscountonly) {
					$cart_total = $cart_total - $product_total_today_db + $firstqtydiscountedamtonetime;
					Log::debug(['[3] $cart_total = $cart_total - $product_total_today_db + $firstqtydiscountedamtonetime;', $cart_total, $product_total_today_db, $firstqtydiscountedamtonetime]);

					$product_total_qty_recurring = $product_total_qty_recurring - $product_recurring_db + $firstqtydiscountedamtrecurring;
					Log::debug(['[0]$product_total_qty_recurring', $product_total_qty_recurring]);
				}

				if (!isset($recurring_cycles_total[$billingcycle])) {
					$recurring_cycles_total[$billingcycle] = 0;
				}
				$recurring_cycles_total[$billingcycle] += $product_total_qty_recurring;
				Log::debug(['[6]$recurring_cycles_total', $recurring_cycles_total]);

				$domain = $productdata["domain"];
				$serverhostname = $productdata["server"]["hostname"] ?? "";
				$serverns1prefix = $productdata["server"]["ns1prefix"] ?? "";
				$serverns2prefix = $productdata["server"]["ns2prefix"] ?? "";
				$serverrootpw = isset($productdata["server"]["rootpw"]) ? (new \App\Helpers\Pwd())->encrypt($productdata["server"]["rootpw"]) : "";

				if ($serverns1prefix && $domain) {
					$serverns1prefix = $serverns1prefix . "." . $domain;
				}
				if ($serverns2prefix && $domain) {
					$serverns2prefix = $serverns2prefix . "." . $domain;
				}

				if ($serverhostname) {
					$serverhostname = trim($serverhostname, " .");
					if (substr_count($serverhostname, ".") > 1 || !$domain) {
						$domain = $serverhostname;
					} else {
						$domain = $serverhostname . "." . $domain;
					}
				}

				$productdata["domain"] = $domain;
				$userid = $auth ? $auth->id : 0;

				// CHECKOUT: Step 3
				if ($checkout) {
					$multiqtyids = [];
					for ($qtycount = 1; $qtycount <= $qty; $qtycount++) {
						if ($firstqtydiscountonly) {
							if ($one_time_discount_applied) {
								$promoid = 0;
							} else {
								$one_time_discount_applied = true;
							}
						}
						$serverid = $servertype ? \App\Module\Server::getServerID($servertype, $servergroup) : "0";
						$hostingquerydates = $databasecycle == "Free Account" ? "0000-00-00" : date("Y-m-d");
						$firstpaymentamount = $firstqtydiscountonly && $qtycount == 1 ? $firstqtydiscountedamtonetime : $product_total_today_db;
						$recurringamount = $firstqtydiscountonly && $qtycount == 1 ? $firstqtydiscountedamtrecurring : $product_recurring_db;

						$serviceid = \App\Models\Hosting::insertGetId([
							"userid" => $userid,
							"orderid" => $orderid,
							"packageid" => $pid,
							"server" => $serverid,
							"regdate" => \Carbon\Carbon::now(),
							"domain" => $domain,
							"paymentmethod" => $paymentmethod,
							"firstpaymentamount" => $firstpaymentamount,
							"amount" => $recurringamount,
							"billingcycle" => $databasecycle,
							"nextduedate" => $hostingquerydates,
							"nextinvoicedate" => $hostingquerydates,
							"domainstatus" => "Pending",
							"ns1" => $serverns1prefix,
							"ns2" => $serverns2prefix,
							"password" => $serverrootpw,
							"promoid" => $promoid ?? 0
						]);

						$multiqtyids[$qtycount] = $serviceid;
						$orderproductids[] = $serviceid;

						if ($stockcontrol) {
							$p = \App\Models\Product::find($pid);
							$p->decrement('qty', 1);
						}

						if ($configoptionsdb) {
							foreach ($configoptionsdb as $confOptionsKey => $value) {
								\App\Models\Hostingconfigoption::insert([
									"relid" => $serviceid,
									"configid" => $confOptionsKey,
									"optionid" => $value["value"],
									"qty" => $value["qty"]
								]);
							}
						}

						foreach ($productdata["customfields"] as $value) {
							\App\Helpers\Customfield::SaveCustomFields($serviceid, [$value["id"] => $value["rawvalue"]], "product", $isAdmin);
						}

						$productdetails = \App\Helpers\ProcessInvoices::getInvoiceProductDetails($serviceid, $pid, date("Y-m-d"), $hostingquerydates, $databasecycle, $domain, $userid);
						$invoice_description = $productdetails["description"];

						if (array_key_exists("sslCompetitiveUpgrade", $productdata) && $productdata["sslCompetitiveUpgrade"]) {
							$invoice_description .= "\n" . Lang::get("store.ssl.competitiveUpgradeQualified");
						}

						$invoice_tax = $productdetails["tax"];

						if (!session("cart.geninvoicedisabled")) {
							$prodinvoicearray = [
								"userid" => $userid,
								"type" => "Hosting",
								"relid" => $serviceid,
								"taxed" => $invoice_tax,
								"duedate" => $hostingquerydates,
								"paymentmethod" => $paymentmethod
							];

							$promo_total_today = $product_total_today_db;
							if ($firstqtydiscountonly && $qty > 1) {
								$promo_total_today -= $onetimediscount;
							}

							if ($product_setup > 0) {
								$prodinvoicesetuparray = $prodinvoicearray;
								$prodinvoicesetuparray["description"] = $productname . " " . $_LANG["ordersetupfee"];
								$prodinvoicesetuparray["amount"] = $product_setup;
								$prodinvoicesetuparray["type"] = "Setup";
								\App\Models\Invoiceitem::insert($prodinvoicesetuparray);
							}

							if ($billingcycle != "free" && $product_onetime >= 0) {
								$prodinvoicearray["description"] = $invoice_description;
								$prodinvoicearray["amount"] = $product_onetime;
								\App\Models\Invoiceitem::insert($prodinvoicearray);
							}

							$promovals = \App\Helpers\ProcessInvoices::getInvoiceProductPromo($promo_total_today, $promoid ?? 0, $userid, $serviceid, $product_setup + $product_onetime);
							if (isset($promovals["description"])) {
								$prodinvoicepromoarray = $prodinvoicearray;
								$prodinvoicepromoarray["type"] = "PromoHosting";
								$prodinvoicepromoarray["description"] = $promovals["description"];
								$prodinvoicepromoarray["amount"] = $promovals["amount"];
								\App\Models\Invoiceitem::insert($prodinvoicepromoarray);
							}
						}

						$adminemailitems = $_LANG["orderproduct"] . ": " . $groupname . " - " . $productname . "<br>\n";
						if ($domain) {
							$adminemailitems .= $_LANG["orderdomain"] . ": " . $domain . "<br>\n";
						}
						foreach ($configurableoptions as $confkey => $value) {
							if (!$value["hidden"]) {
								$adminemailitems .= $value["optionname"] . ": " . $value["selectedname"] . "<br />\n";
							}
						}
						foreach ($customfields as $customfield) {
							if (!$customfield["adminonly"]) {
								$adminemailitems .= (string)$customfield["name"] . ": " . $customfield["value"] . "<br />\n";
							}
						}
						$adminemailitems .= $_LANG["firstpaymentamount"] . ": " . new \App\Helpers\FormatterPrice($product_total_today_db, $currency) . "<br>\n";
						if ($product_recurring_db) {
							$adminemailitems .= $_LANG["recurringamount"] . ": " . new \App\Helpers\FormatterPrice($product_recurring_db, $currency) . "<br>\n";
						}
						$adminemailitems .= $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(["-", " "], "", strtolower($databasecycle))] . "<br>\n";
						if ($allowqty && $qty > 1) {
							$adminemailitems .= $_LANG["quantity"] . ": " . $qty . "<br>\n" . $_LANG["invoicestotal"] . ": " . $productdata["pricing"]["totaltoday"] . "<br>\n";
						}
						$adminemailitems .= "<br>\n";
					}
				}

				$addonsarray = [];
				$addons = $productdata["addons"];
				if ($addons) {
					foreach ($addons as $addonid) {
						$result = \App\Models\Addon::whereId($addonid);
						$data = $result;
						$addon_name = $data->value("name") ?? "";
						$addon_description = $data->value("description") ?? "";
						$addon_billingcycle = $data->value("billingcycle") ?? "";
						$addon_tax = $data->value("tax") ?? "";
						$serverType = $data->value("module") ?? "";
						$serverGroupId = $data->value("server_group_id") ?? 0;
						if (!$CONFIG["TaxEnabled"]) {
							$addon_tax = "";
						}
						switch ($addon_billingcycle) {
							case "recurring":
								$availableAddonCycles = [];
								$data = DB::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency["id"])->where("relid", "=", $addonid)->first();
								$databaseCycles = array("monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
								$databaseSetups = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee");
								foreach ($databaseCycles as $dbCyclesKey => $value) {
									if (0 <= $data->{$value}) {
										$objectKey = $databaseSetups[$dbCyclesKey];
										$availableAddonCycles[$value] = array("price" => $data->{$value}, "setup" => $data->{$objectKey});
									}
								}
								$addon_setupfee = 0;
								$addon_recurring = 0;
								$addon_billingcycle = "Free Account";
								if ($availableAddonCycles) {
									if (array_key_exists($billingcycle, $availableAddonCycles)) {
										$addon_setupfee = $availableAddonCycles[$billingcycle]["setup"];
										$addon_recurring = $availableAddonCycles[$billingcycle]["price"];
										$addon_billingcycle = $billingcycle;
									} else {
										foreach ($availableAddonCycles as $cycle => $data) {
											$addon_setupfee = $data["setup"];
											$addon_recurring = $data["price"];
											$addon_billingcycle = $cycle;
											break;
										}
									}
								}
								break;
							case "free":
							case "Free":
							case "Free Account":
								$addon_setupfee = 0;
								$addon_recurring = 0;
								$addon_billingcycle = "Free";
								break;
							case "onetime":
								$addon_billingcycle = "One Time";
							case "One Time":
							default:
								$result = \App\Models\Pricing::where('type', 'addon')->where('currency', $currency["id"])->where('relid', $addonid);
								$data = $result;
								$addon_setupfee = $data->value("msetupfee") ?? 0;
								$addon_recurring = $data->value("monthly") ?? 0;
								break;
						}
						$hookret = \App\Helpers\Hooks::run_hook("OrderAddonPricingOverride", array("key" => $key, "pid" => $pid, "addonid" => $addonid, "proddata" => $productdata));
						foreach ($hookret as $hookret2) {
							if (is_array($hookret2)) {
								if ($hookret2["setup"]) {
									$addon_setupfee = $hookret2["setup"];
								}
								if ($hookret2["recurring"]) {
									$addon_recurring = $hookret2["recurring"];
								}
							}
						}
						$addon_total_today_db = $addon_setupfee + $addon_recurring;
						$addon_recurring_db = $addon_recurring;
						$addon_total_today = $addon_total_today_db * $qty;
						if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct")) {
							$addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
							$addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
						}
						if ($promotioncode) {
							$onetimediscount = $recurringdiscount = $promoid = 0;
							if ($promocalc = self::CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
								$onetimediscount = $promocalc["onetimediscount"];
								$recurringdiscount = $promocalc["recurringdiscount"];
								$addon_total_today_db -= $onetimediscount;
								$addon_recurring_db -= $recurringdiscount;
								$cart_discount += $onetimediscount * $qty;
							}
						}
						if ($checkout) {
							if ($addon_billingcycle == "Free") {
								$addon_billingcycle = "Free Account";
							}
							for ($qtycount = 1; $qtycount <= $qty; $qtycount++) {
								$serviceid = $multiqtyids[$qtycount];
								$addonsetupfee = $addon_total_today_db - $addon_recurring_db;
								$serverId = $serverType ? \App\Module\Server::getServerID($serverType, $serverGroupId) : "0";
								$aid = \App\Models\Hostingaddon::insertGetId(array("hostingid" => $serviceid, "addonid" => $addonid, "userid" => $userid, "orderid" => $orderid, "server" => $serverId, "regdate" => \Carbon\Carbon::now(), "name" => "", "setupfee" => $addonsetupfee, "recurring" => $addon_recurring_db, "billingcycle" => $addon_billingcycle, "status" => "Pending", "nextduedate" => \Carbon\Carbon::now(), "nextinvoicedate" => \Carbon\Carbon::now(), "paymentmethod" => $paymentmethod, "tax" => $addon_tax));
								$valuesacustomfields = \App\Helpers\Customfield::getCustomFields("addon", $addonid, "", $isAdmin, "", $productdata["acustomfieldsoriginal"]);
								foreach ($valuesacustomfields as $value) {
									\App\Helpers\Customfield::SaveCustomFields($aid, array($value["id"] => $value["rawvalue"]), "addon", $isAdmin);
								}
								$orderaddonids[] = $aid;
								$adminemailitems .= $_LANG["clientareaaddon"] . ": " . $addon_name . "<br>\n" . $_LANG["ordersetupfee"] . ": " . new \App\Helpers\FormatterPrice($addonsetupfee, $currency) . "<br>\n";
								if ($addon_recurring_db) {
									$adminemailitems .= $_LANG["recurringamount"] . ": " . new \App\Helpers\FormatterPrice($addon_recurring_db, $currency) . "<br>\n";
								}
								$adminemailitems .= $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($addon_billingcycle))] . "<br>\n<br>\n";
							}
						}
						$addon_total_today_db *= $qty;
						$cart_total += $addon_total_today_db;
						Log::debug(['[4] $cart_total += $addon_total_today_db;', $cart_total, $addon_total_today_db]);
						$addon_recurring_db *= $qty;
						$addon_billingcycle = str_replace(array("-", " "), "", strtolower($addon_billingcycle));
						if ($addon_tax && (isset($clientsdetails["taxexempt"]) && !$clientsdetails["taxexempt"])) {
							$cart_tax[] = $addon_total_today_db;
							if (!isset($recurring_tax[$addon_billingcycle])) {
								$recurring_tax[$addon_billingcycle] = [];
							}
							$recurring_tax[$addon_billingcycle][] = $addon_recurring_db;
						}
						$recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db;
						Log::debug(['[5]$recurring_cycles_total', $recurring_cycles_total]);
						if ($addon_setupfee == "0" && $addon_recurring == "0") {
							$pricing_text = $_LANG["orderfree"];
						} else {
							$pricing_text = new \App\Helpers\FormatterPrice($addon_recurring, $currency);
							if ($addon_setupfee && $addon_setupfee != "0.00") {
								$pricing_text .= " + " . new \App\Helpers\FormatterPrice($addon_setupfee, $currency) . " " . $_LANG["ordersetupfee"];
							}
							if ($allowqty && 1 < $qty) {
								$pricing_text .= $_LANG["invoiceqtyeach"] . "<br />" . $_LANG["invoicestotal"] . ": " . new \App\Helpers\FormatterPrice($addon_total_today, $currency);
							}
						}
						$addonsarray[] = array("name" => $addon_name, "pricingtext" => $pricing_text, "setup" => 0 < $addon_setupfee ? new \App\Helpers\FormatterPrice($addon_setupfee * $qty, $currency) : "", "recurring" => new \App\Helpers\FormatterPrice($addon_recurring, $currency), "billingcycle" => $addon_billingcycle, "billingcyclefriendly" => Lang::get("client.orderpaymentterm" . $addon_billingcycle), "totaltoday" => new \App\Helpers\FormatterPrice($addon_total_today, $currency));
						$productdata["pricing"]["setup"] += $addon_setupfee * $qty;
						$productdata["pricing"]["addons"] += $addon_recurring * $qty;
						$productdata["pricing"]["recurring"][$addon_billingcycle] = ($productdata["pricing"]["recurring"][$addon_billingcycle] ?? 0) + $addon_recurring * $qty;
						$productdata["pricing"]["totaltoday"] += $addon_total_today;
					}
				}
				$productdata["addons"] = $addonsarray;
				if ($CONFIG["TaxEnabled"] && $tax && (isset($clientsdetails["taxexempt"]) && !$clientsdetails["taxexempt"])) {
					$taxCalculator->setTaxBase($productdata["pricing"]["totaltoday"]);
					$total_tax_1 = $taxCalculator->getLevel1TaxTotal();
					$total_tax_2 = $taxCalculator->getLevel2TaxTotal();
					$productdata["pricing"]["totaltoday"] = $taxCalculator->getTotalAfterTaxes();
					if (0 < $total_tax_1) {
						$productdata["pricing"]["tax1"] = new \App\Helpers\FormatterPrice($total_tax_1, $currency);
					}
					if (0 < $total_tax_2) {
						$productdata["pricing"]["tax2"] = new \App\Helpers\FormatterPrice($total_tax_2, $currency);
					}
				}
				$productdata["pricing"]["productonlysetup"] = 0 < $productdata["pricing"]["productonlysetup"] ? new \App\Helpers\FormatterPrice($productdata["pricing"]["productonlysetup"], $currency) : "";
				$productdata["pricing"]["setup"] = new \App\Helpers\FormatterPrice($productdata["pricing"]["setup"], $currency);
				foreach ($productdata["pricing"]["recurring"] as $cycle => $recurring) {
					unset($productdata["pricing"]["recurring"][$cycle]);
					if (0 < $recurring) {
						$recurringwithtax = $recurring;
						$recurringbeforetax = $recurringwithtax;
						if ($CONFIG["TaxEnabled"] && $tax && (isset($clientsdetails["taxexempt"]) && !$clientsdetails["taxexempt"])) {
							$taxCalculator->setTaxBase($recurring);
							$recurringwithtax = $taxCalculator->getTotalAfterTaxes();
							$recurringbeforetax = $taxCalculator->getTotalBeforeTaxes();
						}
						$productdata["pricing"]["recurring"][$_LANG["orderpaymentterm" . $cycle]] = new \App\Helpers\FormatterPrice($recurringwithtax, $currency);
						$productdata["pricing"]["recurringexcltax"][$_LANG["orderpaymentterm" . $cycle]] = new \App\Helpers\FormatterPrice($recurringbeforetax, $currency);
					}
				}
				if (isset($productdata["pricing"]["addons"]) && 0 < $productdata["pricing"]["addons"]) {
					$productdata["pricing"]["addons"] = new \App\Helpers\FormatterPrice($productdata["pricing"]["addons"], $currency);
				}
				$productdata["pricing"]["totaltoday"] = new \App\Helpers\FormatterPrice($productdata["pricing"]["totaltoday"], $currency);
				$productdata["pricing"]["totaltodayexcltax"] = new \App\Helpers\FormatterPrice($productdata["pricing"]["totaltodayexcltax"], $currency);
				$productdata["pricing"]["totalTodayExcludingTaxSetup"] = new \App\Helpers\FormatterPrice($productdata["pricing"]["totalTodayExcludingTaxSetup"], $currency);
				$productsarray[$key] = $productdata;
			}
			if ($productRemovedFromCart) {
				session()->put('cart.products', array_values(session("cart.products")));
				$cartdata["productRemovedFromCart"] = true;
			}
		}

		$cartdata["products"] = $productsarray;
		$addonsarray = [];
		$cartAddons = $orderForm->getCartDataByKey("addons");
		if (is_array($cartAddons)) {
			$uID = $auth ? $auth->id : 0;
			foreach ($cartAddons as $key => $addon) {
				$addonid = $addon["id"];
				$serviceid = $addon["productid"];
				$service = \App\Models\Hosting::find($serviceid);
				if ($service->clientId != $uID) {
					continue;
				}
				$requested_billingcycle = $addon["billingcycle"] ?? "";
				if (!$requested_billingcycle) {
					$requested_billingcycle = strtolower(str_replace("-", "", $service->billingCycle));
				}
				$result = \App\Models\Addon::find($addonid);
				$data = $result->toArray();
				$addon_name = $data["name"];
				if (array_key_exists("sslCompetitiveUpgrade", $addon) && $addon["sslCompetitiveUpgrade"]) {
					$addon_name .= "<br><small>" . Lang::get("store.ssl.competitiveUpgradeQualified") . "</small>";
				}
				$addon_description = $data["description"];
				$addon_billingcycle = $data["billingcycle"];
				$addon_tax = $data["tax"];
				$serverType = $data["module"];
				$serverGroupId = $data["server_group_id"];
				if (!$CONFIG["TaxEnabled"]) {
					$addon_tax = "";
				}

				switch ($addon_billingcycle) {
					case "recurring":
						$availableAddonCycles = [];
						$data = DB::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency["id"])->where("relid", "=", $addonid)->first();
						$databaseCycles = ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"];
						$databaseSetups = ["msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee"];
						foreach ($databaseCycles as $dbCyclesKey => $value) {
							if ($data->{$value} >= 0) {
								$objectKey = $databaseSetups[$dbCyclesKey];
								$availableAddonCycles[$value] = ["price" => $data->{$value}, "setup" => $data->{$objectKey}];
							}
						}
						$addon_setupfee = 0;
						$addon_recurring = 0;
						$addon_billingcycle = "Free";
						if ($availableAddonCycles) {
							if (array_key_exists($requested_billingcycle, $availableAddonCycles)) {
								$addon_setupfee = $availableAddonCycles[$requested_billingcycle]["setup"];
								$addon_recurring = $availableAddonCycles[$requested_billingcycle]["price"];
								$addon_billingcycle = $requested_billingcycle;
							} else {
								foreach ($availableAddonCycles as $cycle => $data) {
									$addon_setupfee = $data["setup"];
									$addon_recurring = $data["price"];
									$addon_billingcycle = $cycle;
									break;
								}
							}
						}
						break;
					case "free":
					case "Free":
					case "Free Account":
						$addon_setupfee = 0;
						$addon_recurring = 0;
						$addon_billingcycle = "Free";
						break;
					case "onetime":
					case "One Time":
					default:
						$result = \App\Models\Pricing::where('type', 'addon')->where('currency', $currency["id"])->where('relid', $addonid);
						$data = $result;
						$addon_setupfee = $data->value("msetupfee") ?? 0;
						$addon_recurring = $data->value("monthly") ?? 0;
						break;
				}

				$hookret = \App\Helpers\Hooks::run_hook("OrderAddonPricingOverride", ["key" => $key, "addonid" => $addonid, "serviceid" => $serviceid]);
				foreach ($hookret as $hookret2) {
					if (is_array($hookret2)) {
						if ($hookret2["setup"]) {
							$addon_setupfee = $hookret2["setup"];
						}
						if ($hookret2["recurring"]) {
							$addon_recurring = $hookret2["recurring"];
						}
					}
				}

				$addon_total_today_db = $addon_setupfee + $addon_recurring;
				$addon_recurring_db = $addon_recurring;
				if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct")) {
					$addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
					$addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
				}

				if ($promotioncode) {
					$onetimediscount = $recurringdiscount = $promoid = 0;
					if ($promocalc = self::CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
						$onetimediscount = $promocalc["onetimediscount"];
						$recurringdiscount = $promocalc["recurringdiscount"];
						$addon_total_today_db -= $onetimediscount;
						$addon_recurring_db -= $recurringdiscount;
						$cart_discount += $onetimediscount;
					}
				}

        		// CHECKOUT: Step 4
				if ($checkout) {
					$adminemailitems = "";
					if ($addon_billingcycle == "Free") {
						$addon_billingcycle = "Free Account";
					}
					$addonsetupfee = $addon_total_today_db - $addon_recurring_db;
					$serverId = $serverType ? \App\Module\Server::getServerID($serverType, $serverGroupId) : "0";
					$aid = \App\Models\Hostingaddon::insertGetId([
						"hostingid" => $serviceid,
						"addonid" => $addonid,
						"userid" => $userid,
						"orderid" => $orderid,
						"server" => $serverId,
						"regdate" => \Carbon\Carbon::now(),
						"name" => "",
						"setupfee" => $addonsetupfee,
						"recurring" => $addon_recurring_db,
						"billingcycle" => $addon_billingcycle,
						"status" => "Pending",
						"nextduedate" => \Carbon\Carbon::now(),
						"nextinvoicedate" => \Carbon\Carbon::now(),
						"paymentmethod" => $paymentmethod,
						"tax" => $addon_tax
					]);

					$valuesacustomfields = \App\Helpers\Customfield::getCustomFields("addon", $addonid, "", $isAdmin, "", $productdata["acustomfields"] ?? []);
					foreach ($valuesacustomfields as $value) {
						\App\Helpers\Customfield::SaveCustomFields($aid, [$value["id"] => $value["rawvalue"]], "addon", $isAdmin);
					}

					if (array_key_exists("sslCompetitiveUpgrade", $addon) && $addon["sslCompetitiveUpgrade"]) {
						$sslCompetitiveUpgradeAddons = Session::get("SslCompetitiveUpgradeAddons");
						if (!is_array($sslCompetitiveUpgradeAddons)) {
							$sslCompetitiveUpgradeAddons = [];
						}
						$sslCompetitiveUpgradeAddons[] = $aid;
						session(['SslCompetitiveUpgradeAddons' => $sslCompetitiveUpgradeAddons]);
					}

					$orderaddonids[] = $aid;
					$adminemailitems .= $_LANG["clientareaaddon"] . ": " . $addon_name . "<br>\n" . $_LANG["ordersetupfee"] . ": " . new \App\Helpers\FormatterPrice($addonsetupfee, $currency) . "<br>\n";
					if ($addon_recurring_db) {
						$adminemailitems .= $_LANG["recurringamount"] . ": " . new \App\Helpers\FormatterPrice($addon_recurring_db, $currency) . "<br>\n";
					}
					$adminemailitems .= $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(["-", " "], "", strtolower($addon_billingcycle))] . "<br>\n<br>\n";
				}

				$cart_total += $addon_total_today_db;
				Log::debug(['[5]$cart_total += $addon_total_today_db;', $cart_total, $addon_total_today_db]);

				$addon_billingcycle = str_replace(["-", " "], "", strtolower($addon_billingcycle));
				if ($addon_tax && !$clientsdetails["taxexempt"]) {
					$cart_tax[] = $addon_total_today_db;
					if (!isset($recurring_tax[$addon_billingcycle])) {
						$recurring_tax[$addon_billingcycle] = [];
					}
					$recurring_tax[$addon_billingcycle][] = $addon_recurring_db;
				}
				$recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db;
				Log::debug(['[4]$recurring_cycles_total', $recurring_cycles_total]);

				if ($addon_setupfee == "0" && $addon_recurring == "0") {
					$pricing_text = $_LANG["orderfree"];
				} else {
					$pricing_text = new \App\Helpers\FormatterPrice($addon_recurring, $currency);
					if ($addon_setupfee && $addon_setupfee != "0.00") {
						$pricing_text .= " + " . new \App\Helpers\FormatterPrice($addon_setupfee, $currency) . " " . $_LANG["ordersetupfee"];
					}
				}

				$result = \App\Models\Hosting::selectRaw("tblproducts.name,tblhosting.packageid,tblhosting.domain")->where("tblhosting.id", $serviceid)->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid");
				$data = $result;
				$productname = $isAdmin ? $data->value("name") : \App\Models\Product::getProductName($data->value("packageid"));
				$domainname = $data->value("domain");

				$addonsarray[] = [
					"addonid" => $addonid,
					"name" => $addon_name,
					"productname" => $productname,
					"domainname" => $domainname,
					"pricingtext" => $pricing_text,
					"setup" => $addon_setupfee > 0 ? new \App\Helpers\FormatterPrice($addon_setupfee, $currency) : "",
					"totaltoday" => new \App\Helpers\FormatterPrice($addon_setupfee + $addon_recurring, $currency),
					"billingcycle" => $addon_billingcycle,
					"billingcyclefriendly" => Lang::get("client.orderpaymentterm" . $addon_billingcycle)
				];
			}
		}

		$cartdata["addons"] = $addonsarray;
		$totaldomainprice = 0;
		$cartDomains = $orderForm->getCartDataByKey("domains");
		if (is_array($cartDomains)) {
			$result = \App\Models\Pricing::where([
				"type" => "domainaddons", 
				"currency" => $currency["id"], 
				"relid" => 0
			]);
			$data = $result;
			
			$domaindnsmanagementprice = $data->value("msetupfee") ?? 0;
			$domainemailforwardingprice = $data->value("qsetupfee") ?? 0;
			$domainidprotectionprice = $data->value("ssetupfee") ?? 0;
		
			foreach ($cartDomains as $key => $domain) {
				$domaintype = $domain["type"];
				$domainname = $domain["domain"];
				$regperiod = $domain["regperiod"];
				$domainPriceOverride = $domain["domainpriceoverride"] ?? null;
				$domainRenewOverride = $domain["domainrenewoverride"] ?? null;
		
				$domainparts = explode(".", $domainname, 2);
				list($sld, $tld) = $domainparts;
				$temppricelist = \App\Helpers\Domain::getTLDPriceList("." . $tld);
		
				if (!isset($temppricelist[$regperiod][$domaintype])) {
					$tldyears = array_keys($temppricelist);
					$regperiod = array_key_exists(0, $tldyears) ? $tldyears[0] : 1;
				}
		
				if (!isset($temppricelist[$regperiod][$domaintype])) {
					$errMsg = "Invalid TLD/Registration Period Supplied for Domain Registration";
					if (Application::isApiRequest()) {
						return ["result" => "error", "message" => $errMsg];
					}
					throw new \App\Exceptions\Fatal($errMsg);
				}
		
				if (array_key_exists($domainname, $freedomains)) {
					$tldyears = array_keys($temppricelist);
					$regperiod = $tldyears[0];
					$domainprice = "0.00";
					$renewprice = $freedomains[$domainname] == "once" ? 
						$temppricelist[$regperiod]["renew"] : "0.00";
				} else {
					$domainprice = $temppricelist[$regperiod][$domaintype];
					$renewprice = $temppricelist[$regperiod]["renew"];
				}
		
				$before_priceoverride_value = "";
				if ($bundleoverride = \App\Helpers\Cart::bundlesGetProductPriceOverride("domain", $key)) {
					$before_priceoverride_value = $domainprice;
					$domainprice = $renewprice = $bundleoverride;
				}
				
				if (!is_null($domainPriceOverride)) {
					$domainprice = $domainPriceOverride;
				}
				
				if (!is_null($domainRenewOverride)) {
					$renewprice = $domainRenewOverride;
				}
		
				$hookret = \App\Helpers\Hooks::run_hook("OrderDomainPricingOverride", [
					"type" => $domaintype,
					"domain" => $domainname,
					"regperiod" => $regperiod,
					"dnsmanagement" => $domain["dnsmanagement"] ?? "",
					"emailforwarding" => $domain["emailforwarding"] ?? "",
					"idprotection" => $domain["idprotection"] ?? "",
					"eppcode" => \App\Helpers\Sanitize::decode($domain["eppcode"] ?? ""),
					"premium" => isset($domain["isPremium"]) ? (int)$domain["isPremium"] : 0
				]);
		
				foreach ($hookret as $hookret2) {
					if (is_array($hookret2)) {
						if (isset($hookret2["firstPaymentAmount"])) {
							$before_priceoverride_value = $domainprice;
							$domainprice = $hookret2["firstPaymentAmount"];
						}
						if (isset($hookret2["recurringAmount"])) {
							$renewprice = $hookret2["recurringAmount"];
						}
					} elseif (strlen($hookret2)) {
						$before_priceoverride_value = $domainprice;
						$domainprice = $hookret2;
					}
				}
		
				$dnsmanagement = false;
				if (isset($domain["dnsmanagement"]) && $domain["dnsmanagement"]) {
					$dnsmanagement = true;
					$domainprice += $domaindnsmanagementprice * $regperiod;
					$renewprice += $domaindnsmanagementprice * $regperiod;
					if (strlen($before_priceoverride_value)) {
						$before_priceoverride_value += $domaindnsmanagementprice * $regperiod;
					}
				}
		
				$emailforwarding = false;
				if (isset($domain["emailforwarding"]) && $domain["emailforwarding"]) {
					$emailforwarding = true;
					$domainprice += $domainemailforwardingprice * $regperiod;
					$renewprice += $domainemailforwardingprice * $regperiod;
					if (strlen($before_priceoverride_value)) {
						$before_priceoverride_value += $domainemailforwardingprice * $regperiod;
					}
				}
		
				$idprotection = false;
				if (isset($domain["idprotection"]) && $domain["idprotection"]) {
					$idprotection = true;
					$domainprice += $domainidprotectionprice * $regperiod;
					$renewprice += $domainidprotectionprice * $regperiod;
					if (strlen($before_priceoverride_value)) {
						$before_priceoverride_value += $domainidprotectionprice * $regperiod;
					}
				}
		
				if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct")) {
					$domainprice = round($domainprice / $excltaxrate, 2);
					$renewprice = round($renewprice / $excltaxrate, 2);
				}
		
				$domain_price_db = $domainprice;
				$domain_renew_price_db = $renewprice;
		
				if ($promotioncode) {
					$onetimediscount = $recurringdiscount = $promoid = 0;
					if ($promocalc = self::CalcPromoDiscount("D." . $tld, $regperiod . "Years", $domain_price_db, $domain_renew_price_db)) {
						$onetimediscount = $promocalc["onetimediscount"];
						$recurringdiscount = $promocalc["recurringdiscount"];
						$domain_price_db -= $onetimediscount;
						$domain_renew_price_db -= $recurringdiscount;
						$cart_discount += $onetimediscount;
						$promoid = $promo_data["id"];
					}
				}
		
				switch ($regperiod) {
					case "1":
						$domain_billing_cycle = "annually";
						break;
					case "2":
						$domain_billing_cycle = "biennially";
						break;
					case "3":
						$domain_billing_cycle = "triennially";
						break;
					default:
						$domain_billing_cycle = "annually";
						break;
				}
		
				if (!is_null($domain_renew_price_db)) {
					if ($CONFIG["TaxEnabled"] && $CONFIG["TaxDomains"] && !$clientsdetails["taxexempt"]) {
						if (!isset($recurring_tax[$domain_billing_cycle])) {
							$recurring_tax[$domain_billing_cycle] = [];
						}
						$recurring_tax[$domain_billing_cycle][] = $domain_renew_price_db;
					}
					if (array_key_exists($domain_billing_cycle ?? "", $recurring_cycles_total)) {
						$recurring_cycles_total[$domain_billing_cycle] += $domain_renew_price_db;
					}
				}
		
				if ($checkout) {
					$donotrenew = Cfg::get("DomainAutoRenewDefault") ? 0 : 1;
		
					$domainid = \App\Models\Domain::insertGetId([
						"userid" => $userid,
						"orderid" => $orderid,
						"type" => $domaintype,
						"registrationdate" => \Carbon\Carbon::now()->format('Y-m-d'),
						"domain" => $domainname,
						"firstpaymentamount" => $domain_price_db,
						"recurringamount" => $domain_renew_price_db,
						"registrationperiod" => $regperiod,
						"status" => "Pending",
						"paymentmethod" => $paymentmethod,
						"expirydate" => "00000000",
						"nextduedate" => \Carbon\Carbon::now()->format('Y-m-d'),
						"nextinvoicedate" => \Carbon\Carbon::now()->format('Y-m-d'),
						"dnsmanagement" => (int)$dnsmanagement,
						"emailforwarding" => (int)$emailforwarding,
						"idprotection" => (int)$idprotection,
						"donotrenew" => (int)$donotrenew,
						"promoid" => $promoid ?? 0,
						"is_premium" => isset($domain["isPremium"]) ? (int)$domain["isPremium"] : 0
					]);
		
					// Save registrar cost details if available
					if (array_key_exists("registrarCostPrice", $domain)) {
						\App\Models\DomainsExtra::updateOrCreate(
							["domain_id" => $domainid, "name" => "registrarCostPrice"],
							["value" => $domain["registrarCostPrice"]]
						);
						
						\App\Models\DomainsExtra::updateOrCreate(
							["domain_id" => $domainid, "name" => "registrarCurrency"],
							["value" => (int)$domain["registrarCurrency"]]
						);
					}
		
					if ((isset($domain["isPremium"]) && $domain["isPremium"]) && 
						array_key_exists("registrarRenewalCostPrice", $domain)) {
						\App\Models\DomainsExtra::updateOrCreate(
							["domain_id" => $domainid, "name" => "registrarRenewalCostPrice"],
							["value" => $domain["registrarRenewalCostPrice"]]
						);
					}
		
					$orderdomainids[] = $domainid;
		
					$adminemailitems = "";
					$adminemailitems .= $_LANG["orderdomainregistration"] . ": " . ucfirst($domaintype) . "<br>\n" 
									. $_LANG["orderdomain"] . ": " . $domainname . "<br>\n"
									. $_LANG["firstpaymentamount"] . ": " . new \App\Helpers\FormatterPrice($domain_price_db, $currency) . "<br>\n"
									. $_LANG["recurringamount"] . ": " . new \App\Helpers\FormatterPrice($domain_renew_price_db, $currency) . "<br>\n"
									. $_LANG["orderregperiod"] . ": " . $regperiod . " " . $_LANG["orderyears"] . "<br>\n";
		
					if ($dnsmanagement) {
						$adminemailitems .= " + " . $_LANG["domaindnsmanagement"] . "<br>\n";
					}
					if ($emailforwarding) {
						$adminemailitems .= " + " . $_LANG["domainemailforwarding"] . "<br>\n";
					}
					if ($idprotection) {
						$adminemailitems .= " + " . $_LANG["domainidprotection"] . "<br>\n";
					}
					$adminemailitems .= "<br>\n";
		
					// if (in_array($domaintype, ["register", "transfer"])) {
					// 	$additflds = new \App\Helpers\AdditionalFields();
					// 	$additflds->setTLD($tld)
					// 			 ->setDomainType($domaintype)
					// 			 ->setFieldValues($domain["fields"])
					// 			 ->saveToDatabase($domainid);
					// }
		
					if ($domaintype == "transfer" && $domain["eppcode"]) {
						$domaineppcodes[$domainname] = $domain["eppcode"];
					}
				}
		
				$pricing_text = "";
				if (strlen($before_priceoverride_value)) {
					$pricing_text .= "<strike>" . new \App\Helpers\FormatterPrice($before_priceoverride_value, $currency) . "</strike> ";
				}
				$pricing_text .= new \App\Helpers\FormatterPrice($domainprice, $currency);
		
				$pricing = \App\Helpers\Domain::getTLDPriceList(
					"." . $tld, 
					true, 
					$domaintype == "transfer" ? "transfer" : ""
				);
		
				if (array_key_exists($domainname, $freedomains)) {
					$pricing = [key($pricing) => current($pricing)];
				}
		
				$tempdomains[$key] = [
					"type" => $domaintype,
					"domain" => $domainname,
					"regperiod" => $regperiod,
					"yearsLanguage" => $regperiod == 1 ? Lang::get("orderForm.year") : Lang::get("orderForm.years"),
					"shortYearsLanguage" => $regperiod == 1 ? 
						Lang::get("orderForm.shortPerYear", ["years" => $regperiod]) : 
						Lang::get("orderForm.shortPerYears", ["years" => $regperiod]),
					"price" => $pricing_text,
					"totaltoday" => new \App\Helpers\FormatterPrice($domainprice, $currency),
					"renewprice" => new \App\Helpers\FormatterPrice($renewprice, $currency),
					"dnsmanagement" => $dnsmanagement,
					"emailforwarding" => $emailforwarding,
					"idprotection" => $idprotection,
					"eppvalue" => $domain["eppcode"] ?? "",
					"premium" => isset($domain["isPremium"]) ? $domain["isPremium"] : 0,
					"pricing" => !is_null($domainPriceOverride) ? [$regperiod => $pricing_text] : $pricing
				];
		
				if (!$domain_renew_price_db) {
					unset($tempdomains[$key]["renewprice"]);
				}
		
				$totaldomainprice += $domain_price_db;
			}
		}

		$cartdata["domains"] = $tempdomains;
		$cart_total += $totaldomainprice;
		Log::debug(['[6] $cart_total += $totaldomainprice;', $cart_total, $totaldomainprice]);

		if ($CONFIG["TaxDomains"]) {
			$cart_tax[] = $totaldomainprice;
		}

		$orderUpgradeIds = [];
		$cartdata["upgrades"] = [];
		$cartUpgrades = $orderForm->getCartDataByKey("upgrades");
		if (is_array($cartUpgrades)) {
			foreach ($cartUpgrades as $cartUpgrade) {
				$entityType = $cartUpgrade["upgrade_entity_type"];
				$entityId = $cartUpgrade["upgrade_entity_id"];
				$targetEntityId = $cartUpgrade["target_entity_id"];
				$upgradeCycle = $cartUpgrade["billing_cycle"];
		
				try {
					if ($entityType == "service") {
						$upgradeEntity = \App\Models\Hosting::findOrFail($entityId);
						$upgradeTarget = \App\Models\Product::findOrFail($targetEntityId);
					} elseif ($entityType == "addon") {
						$upgradeEntity = \App\Models\Hostingaddon::findOrFail($entityId);
						$upgradeTarget = \App\Models\Addon::findOrFail($targetEntityId);
					} else {
						continue;
					}
				} catch (\Exception $e) {
					continue;
				}
		
				// Verify client ownership
				if ($upgradeEntity->clientId != $auth->id) {
					continue;
				}
		
				// Calculate upgrade details
				$upgrade = (new \App\Helpers\UpgradeCalculator())
					->setUpgradeTargets($upgradeEntity, $upgradeTarget, $upgradeCycle)
					->calculate();
		
				// Add to cart data
				$cartdata["upgrades"][] = $upgrade;
				
				// Update cart total
				$cart_total += $upgrade->upgradeAmount->toNumeric();
				Log::debug([
					'[7] $cart_total += $upgrade->upgradeAmount->toNumeric();', 
					$cart_total, 
					$upgrade->upgradeAmount->toNumeric()
				]);
		
				// Handle tax calculation
				if ($upgrade->applyTax) {
					$cart_tax[] = $upgrade->upgradeAmount->toNumeric();
				}
		
				// Process checkout if needed
				if ($checkout) {
					// Save upgrade details
					$upgrade->userId = $userid;
					$upgrade->orderId = $orderid;
					$upgrade->upgradeAmount = $upgrade->upgradeAmount->toNumeric();
					$upgrade->creditAmount = $upgrade->creditAmount->toNumeric();
					$upgrade->newRecurringAmount = $upgrade->newRecurringAmount->toNumeric();
					$upgrade->save();
		
					// Prepare invoice description
					$invoiceDescription = Lang::get("admin.upgrade") . ": ";
		
					if ($upgrade->type == "service") {
						$invoiceDescription .= $upgrade->originalProduct->productGroup->name 
							. " - " 
							. $upgrade->originalProduct->name 
							. " => " 
							. $upgrade->newProduct->name;
		
						if ($upgrade->service->domain) {
							$invoiceDescription .= "\n" . $upgrade->service->domain;
						}
					} elseif ($upgrade->type == "addon") {
						$invoiceDescription .= $upgrade->originalAddon->name 
							. " => " 
							. $upgrade->newAddon->name;
					}
		
					// Add recurring amount to description
					$invoiceDescription .= "\n" . "New Recurring Amount: " 
						. \App\Helpers\Format::formatCurrency($upgrade->newRecurringAmount);
		
					// Add credit information if applicable
					if (0 < $upgrade->totalDaysInCycle) {
						$invoiceDescription .= "\n" . "Credit Amount: " 
							. \App\Helpers\Format::formatCurrency($upgrade->creditAmount) 
							. "\n" 
							. Lang::get("admin.upgradeCreditDescription", [
								"daysRemaining" => $upgrade->daysRemaining,
								"totalDays" => $upgrade->totalDaysInCycle
							]);
					}
		
					// Create invoice item
					\App\Models\Invoiceitem::insert([
						"userid" => $userid,
						"type" => "Upgrade",
						"relid" => $upgrade->id,
						"description" => $invoiceDescription,
						"amount" => $upgrade->upgradeAmount,
						"taxed" => $upgrade->applyTax,
						"duedate" => \Carbon\Carbon::now(),
						"paymentmethod" => $paymentmethod
					]);
		
					$orderUpgradeIds[] = $upgrade->id;
				}
			}
		}

		$orderrenewals = "";
		$cartdata["renewals"] = [];
		$cartRenewals = $orderForm->getCartDataByKey("renewals");
		if (is_array($cartRenewals)) {
			$result = \App\Models\Pricing::where([
				"type" => "domainaddons",
				"currency" => $currency["id"],
				"relid" => 0
			]);
			$data = $result;
			$domaindnsmanagementprice = $data->value("msetupfee");
			$domainemailforwardingprice = $data->value("qsetupfee");
			$domainidprotectionprice = $data->value("ssetupfee");

			foreach ($cartRenewals as $domainid => $regperiod) {
				try {
					$domain = \App\Models\Domain::findOrFail($domainid);
				} catch (\Exception $e) {
					continue;
				}

				$domainid = $domain->id;
				$userId = $domain->clientId;
				if ($userId != $auth->id) {
					continue;
				}

				$clientCurrency = \App\Helpers\Format::getCurrency($userId);
				$domainname = $domain->domain;

				$expirydate = $domain->expiryDate;
				if ($domain->getRawAttribute("expirydate") == "0000-00-00") {
					$expirydate = $domain->nextDueDate;
				}

				$dnsmanagement = $domain->hasDnsManagement;
				$emailforwarding = $domain->hasEmailForwarding;
				$idprotection = $domain->hasIdProtection;
				$tld = "." . $domain->tld;
				$isPremium = $domain->isPremium;

				$temppricelist = \App\Helpers\Domain::getTLDPriceList($tld, "", true);
				
				if (!isset($temppricelist[$regperiod]["renew"])) {
					$errMsg = "Invalid TLD/Registration Period Supplied for Domain Renewal";
					if (Application::isApiRequest()) {
						return ["result" => "error", "message" => $errMsg];
					}
					throw new \App\Exceptions\Fatal($errMsg);
				}

				$renewprice = $temppricelist[$regperiod]["renew"];

				if ($isPremium) {
					$extraDetails = \App\Models\DomainsExtra::whereDomainId($domainid)
						->whereName("registrarRenewalCostPrice")
						->first();

					if ($extraDetails) {
						$regperiod = 1;
						$markupRenewalPrice = $extraDetails->value;
						$domainRecurringPrice = (float) \App\Helpers\Functions::format_as_currency($domain->recurringAmount);
						$markupPercentage = \App\Models\DomainpricingPremium::markupForCost($markupRenewalPrice);
						$markupRenewalPrice = (float) \App\Helpers\Functions::format_as_currency(
							$markupRenewalPrice * (1 + $markupPercentage / 100)
						);

						// Determine final renewal price for premium domains
						if ($domainRecurringPrice == $markupRenewalPrice) {
							$renewprice = $domainRecurringPrice;
						} elseif ($markupRenewalPrice <= $domainRecurringPrice) {
							$renewprice = $domainRecurringPrice;
						} else {
							$renewprice = $markupRenewalPrice;
						}
					}
				}

				$renewalGracePeriod = $domain->gracePeriod;
				$gracePeriodFee = $domain->gracePeriodFee;
				$redemptionGracePeriod = $domain->redemptionGracePeriod;
				$redemptionGracePeriodFee = $domain->redemptionGracePeriodFee;

				if (0 < $gracePeriodFee) {
					$gracePeriodFee = \App\Helpers\Format::convertCurrency($gracePeriodFee, 1, $clientCurrency["id"]);
				}
				if (0 < $redemptionGracePeriodFee) {
					$redemptionGracePeriodFee = \App\Helpers\Format::convertCurrency($redemptionGracePeriodFee, 1, $clientCurrency["id"]);
				}
				if (!$renewalGracePeriod || $renewalGracePeriod < 0 || $gracePeriodFee < 0) {
					$renewalGracePeriod = 0;
					$gracePeriodFee = 0;
				}
				if (!$redemptionGracePeriod || $redemptionGracePeriod < 0 || $redemptionGracePeriodFee < 0) {
					$redemptionGracePeriod = 0;
					$redemptionGracePeriodFee = 0;
				}
				$today = \App\Helpers\Carbon::today();
				$todayExpiryDifference = $today->diff($expirydate);
				$daysUntilExpiry = ($todayExpiryDifference->invert == 1 ? -1 : 1) * $todayExpiryDifference->days;
				
				// Check grace period status
				$inGracePeriod = $inRedemptionGracePeriod = false;
				if ($daysUntilExpiry < 0) {
					if ($renewalGracePeriod && 0 - $renewalGracePeriod <= $daysUntilExpiry) {
						$inGracePeriod = true;
					} elseif ($redemptionGracePeriod && 0 - ($renewalGracePeriod + $redemptionGracePeriod) <= $daysUntilExpiry) {
						$inRedemptionGracePeriod = true;
					}
		
					if (($inGracePeriod || $inRedemptionGracePeriod) && !$isPremium) {
						$renewalOptions = reset($temppricelist);
						$regperiod = reset(array_keys($temppricelist));
						$renewprice = $renewalOptions["renew"];
					}
				}

				if ($dnsmanagement) {
					$renewprice += $domaindnsmanagementprice * $regperiod;
				}
				if ($emailforwarding) {
					$renewprice += $domainemailforwardingprice * $regperiod;
				}
				if ($idprotection) {
					$renewprice += $domainidprotectionprice * $regperiod;
				}
				if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxInclusiveDeduct")) {
					$renewprice = round($renewprice / $excltaxrate, 2);
				}

				$domain_renew_price_db = $renewprice;
				if ($promotioncode) {
					$onetimediscount = $recurringdiscount = $promoid = 0;
					if ($promocalc = self::CalcPromoDiscount("D" . $tld, $regperiod . "Years", $domain_renew_price_db, $domain_renew_price_db)) {
						$onetimediscount = $promocalc["onetimediscount"];
						$domain_renew_price_db -= $onetimediscount;
						$cart_discount += $onetimediscount;
					}
				}

				// Update cart total
				$cart_total += $domain_renew_price_db;
				Log::debug(['[8] $cart_total += $domain_renew_price_db;', $cart_total, $domain_renew_price_db]);

				// Handle checkout process
				if ($checkout) {
					$adminemailitems = "";
					$domaindesc = $_LANG["domainrenewal"] . " - " . $domainname . " - " . $regperiod . " " . $_LANG["orderyears"];
					
					if ($dnsmanagement) {
						$adminemailitems .= " + " . $_LANG["domaindnsmanagement"] . "<br>\n";
						$domaindesc .= "\n + " . $_LANG["domaindnsmanagement"];
					}
					if ($emailforwarding) {
						$adminemailitems .= " + " . $_LANG["domainemailforwarding"] . "<br>\n";
						$domaindesc .= "\n + " . $_LANG["domainemailforwarding"];
					}
					if ($idprotection) {
						$adminemailitems .= " + " . $_LANG["domainidprotection"] . "<br>\n";
						$domaindesc .= "\n + " . $_LANG["domainidprotection"];
					}
					$adminemailitems .= "<br>\n";

					// Create invoice items
					$tax = Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxDomains") ? "1" : "0";
					$domain->registrationPeriod = $regperiod;
					$domain->recurringAmount = $domain_renew_price_db;

					\App\Models\Invoiceitem::insert([
						"userid" => $userid,
						"type" => "Domain",
						"relid" => $domainid,
						"description" => $domaindesc,
						"amount" => $domain_renew_price_db,
						"taxed" => $tax,
						"duedate" => \Carbon\Carbon::now(),
						"paymentmethod" => $paymentmethod
					]);

					// Handle grace period fees
					if ($inGracePeriod || $inRedemptionGracePeriod) {
						if (0 < $gracePeriodFee) {
							DB::table("tblinvoiceitems")->insert([
								"userid" => $userId,
								"type" => "DomainGraceFee",
								"relid" => $domainid,
								"description" => Lang::get("domainGracePeriodFeeInvoiceItem", ["domainName" => $domainname]),
								"amount" => $gracePeriodFee,
								"taxed" => $tax,
								"duedate" => $today->toDateString(),
								"paymentmethod" => $paymentmethod
							]);
						}
						if ($domain->status == "Active") {
							$domain->status = "Grace";
						}
					}

					// Handle redemption period fees
					if ($inRedemptionGracePeriod) {
						if (0 < $redemptionGracePeriodFee) {
							DB::table("tblinvoiceitems")->insert([
								"userid" => $userId,
								"type" => "DomainRedemptionFee",
								"relid" => $domainid,
								"description" => Lang::get("domainRedemptionPeriodFeeInvoiceItem", ["domainName" => $domainname]),
								"amount" => $redemptionGracePeriodFee,
								"taxed" => $tax,
								"duedate" => $today->toDateString(),
								"paymentmethod" => $paymentmethod
							]);
						}
						if (in_array($domain->status, ["Active", "Grace"])) {
							$domain->status = "Redemption";
						}
					}
					$domain->save();

					// Handle existing unpaid invoices
					$result = \App\Models\Invoiceitem::selectRaw("tblinvoiceitems.id,tblinvoiceitems.invoiceid")
						->where([
							"type" => "Domain",
							"relid" => $domainid,
							"status" => "Unpaid",
							"tblinvoices.userid" => $auth->id
						])
						->join("tblinvoices", "tblinvoices.id", "=", "tblinvoiceitems.invoiceid")
						->get();

					foreach ($result->toArray() as $data) {
						$itemid = $data["id"];
						$invoiceid = $data["invoiceid"];
						$otherItems = \App\Models\Invoiceitem::where("invoiceid", $invoiceid)->where("id", "!=", $itemid);
						$itemCount = $otherItems->count();

						foreach ($otherItems->get() as $otherItem) {
							switch ($otherItem->type) {
								case "DomainGraceFee":
								case "DomainRedemptionFee":
								case "PromoDomain":
									if ($otherItem->relatedEntityId == $domainid) {
										$itemCount--;
									}
									break;
								case "GroupDiscount":
								case "LateFee":
									$itemCount--;
									break;
							}
						}

						if ($itemCount === 0) {
							\App\Models\Invoice::where(["id" => $invoiceid])->update(["status" => "Cancelled"]);
							LogActivity::Save("Cancelled Previous Domain Renewal Invoice - Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $userId);
							\App\Helpers\Hooks::run_hook("InvoiceCancelled", ["invoiceid" => $invoiceid]);
						} else {
							\App\Models\Invoiceitem::where(function ($query) use ($invoiceid, $domainid) {
								$query->where("invoiceid", $invoiceid)
									->where("relid", $domainid)
									->whereIn("type", ["Domain", "DomainGraceFee", "DomainRedemptionFee", "PromoDomain"]);
							})->orWhere(function ($query) use ($invoiceid) {
								$query->where("invoiceid", $invoiceid)
									->whereIn("type", ["GroupDiscount", "LateFee"]);
							})->delete();
							\App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
							LogActivity::Save("Removed Previous Domain Renewal Line Item - Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $userId);
						}
					}
				}

				// Calculate final renewal price with fees
				$renewalPrice = $renewprice;
				$hasGracePeriodFee = $hasRedemptionGracePeriodFee = false;

				if (($inGracePeriod || $inRedemptionGracePeriod) && $gracePeriodFee != "0.00") {
					$cart_total += $gracePeriodFee;
					Log::debug(['[9] $cart_total += $gracePeriodFee;', $cart_total, $gracePeriodFee]);
					$renewalPrice += $gracePeriodFee;
					if ($CONFIG["TaxDomains"]) {
						$cart_tax[] = $gracePeriodFee;
					}
					$hasGracePeriodFee = true;
				}

				if ($inRedemptionGracePeriod && $redemptionGracePeriodFee != "0.00") {
					$cart_total += $redemptionGracePeriodFee;
					Log::debug(['[10] $cart_total += $redemptionGracePeriodFee;', $cart_total, $redemptionGracePeriodFee]);
					$renewalPrice += $redemptionGracePeriodFee;
					if ($CONFIG["TaxDomains"]) {
						$cart_tax[] = $redemptionGracePeriodFee;
					}
					$hasRedemptionGracePeriodFee = true;
				}

				// Calculate taxes
				$renewalTax = [];
				$renewalPriceBeforeTax = $renewalPrice;
				if (Cfg::getValue("TaxEnabled") && Cfg::getValue("TaxDomains") && !$clientsdetails["taxexempt"]) {
					$taxCalculator->setTaxBase($renewalPrice);
					$total_tax_1 = $taxCalculator->getLevel1TaxTotal();
					$total_tax_2 = $taxCalculator->getLevel2TaxTotal();
					
					if ($total_tax_1 > 0) {
						$renewalTax["tax1"] = new \App\Helpers\FormatterPrice($total_tax_1, $currency);
					}
					if ($total_tax_2 > 0) {
						$renewalTax["tax2"] = new \App\Helpers\FormatterPrice($total_tax_2, $currency);
					}
					if (Cfg::getValue("TaxType") == "Inclusive") {
						$renewalPriceBeforeTax = $taxCalculator->getTotalBeforeTaxes();
					}
				}

				// Add to cart data
				$cartdata["renewals"][$domainid] = [
					"domain" => $domainname,
					"regperiod" => $regperiod,
					"price" => new \App\Helpers\FormatterPrice($renewalPrice, $currency),
					"priceBeforeTax" => new \App\Helpers\FormatterPrice($renewalPriceBeforeTax, $currency),
					"taxes" => $renewalTax,
					"dnsmanagement" => $dnsmanagement,
					"emailforwarding" => $emailforwarding,
					"idprotection" => $idprotection,
					"hasGracePeriodFee" => $hasGracePeriodFee,
					"hasRedemptionGracePeriodFee" => $hasRedemptionGracePeriodFee
				];
			}
		}
		// $cart = session()->get('cart');
		// if($cart) {
		// 	dump('$cart : ');
		// 	dump($cart);
		// }

		$cart_adjustments = 0;
		$adjustments = \App\Helpers\Hooks::run_hook("CartTotalAdjustment", session("cart") ?? []);

		// Tambahkan adjustments dari session jika ada
		if (session()->has('cart.adjustments')) {
			$sessionAdjustments = session('cart.adjustments');
			if (!is_array($sessionAdjustments)) {
				$sessionAdjustments = [$sessionAdjustments];
			}
			$adjustments = array_merge($adjustments, $sessionAdjustments);
		}

		// if ($adjustments) {
		// 	dump('$adjustments : ');
		// 	dump($adjustments);
		// }
		foreach ($adjustments as $k => $adjvals) {
			if ($checkout) {
				\App\Models\Invoiceitem::insert([
					"userid" => $userid,
					"type" => "",
					"relid" => "",
					"description" => $adjvals["description"] ?? "",
					"amount" => $adjvals["amount"] ?? 0,
					"taxed" => $adjvals["taxed"] ?? false,
					"duedate" => \Carbon\Carbon::now(),
					"paymentmethod" => $paymentmethod
				]);
			}
			$adjustments[$k]["amount"] = new \App\Helpers\FormatterPrice($adjvals["amount"] ?? 0, $currency);
			$cart_adjustments += $adjvals["amount"];
			if (isset($adjvals["taxed"]) && $adjvals["taxed"]) {
				$cart_tax[] = $adjvals["amount"];
			}
		}

		$total_tax_1 = $total_tax_2 = 0;
		if ($CONFIG["TaxEnabled"] && !$clientsdetails["taxexempt"]) {
			if (Cfg::getValue("TaxPerLineItem")) {
				foreach ($cart_tax as $taxBase) {
					$taxCalculator->setTaxBase($taxBase);
					$total_tax_1 += $taxCalculator->getLevel1TaxTotal();
					$total_tax_2 += $taxCalculator->getLevel2TaxTotal();
				}
			} else {
				$taxCalculator->setTaxBase(array_sum($cart_tax));
				$total_tax_1 = $taxCalculator->getLevel1TaxTotal();
				$total_tax_2 = $taxCalculator->getLevel2TaxTotal();
			}
			if ($CONFIG["TaxType"] == "Inclusive") {
				$cart_total -= $total_tax_1 + $total_tax_2;
				Log::debug(['[10] $cart_total -= $total_tax_1 + $total_tax_2;', $cart_total, $total_tax_1, $total_tax_2]);
			} else {
				foreach ($recurring_tax as $cycle => $taxBases) {
					if (Cfg::getValue("TaxPerLineItem")) {
						foreach ($taxBases as $taxBase) {
							$taxCalculator->setTaxBase($taxBase);
							$recurring_cycles_total[$cycle] += $taxCalculator->getLevel1TaxTotal() + $taxCalculator->getLevel2TaxTotal();
							Log::debug(['[1]$recurring_cycles_total', $recurring_cycles_total]);
						}
					} else {
						$taxCalculator->setTaxBase(array_sum($taxBases));
						$recurring_cycles_total[$cycle] += $taxCalculator->getLevel1TaxTotal() + $taxCalculator->getLevel2TaxTotal();
						Log::debug(['[0]$recurring_cycles_total', $recurring_cycles_total]);
					}
				}
			}
		}

		$cart_subtotal = $cart_total + $cart_discount;
		if($adjustments) {
			$cart_subtotal += $cart_adjustments;
		}
		$cart_total += $total_tax_1 + $total_tax_2 + $cart_adjustments;
		Log::debug(['[11] $cart_total += $total_tax_1 + $total_tax_2 + $cart_adjustments;', $cart_total, $total_tax_1, $total_tax_2, $cart_adjustments]);

		$cart_subtotal = \App\Helpers\Functions::format_as_currency($cart_subtotal);
		$cart_discount = \App\Helpers\Functions::format_as_currency($cart_discount);
		$cart_adjustments = \App\Helpers\Functions::format_as_currency($cart_adjustments);
		$total_tax_1 = \App\Helpers\Functions::format_as_currency($total_tax_1);
		$total_tax_2 = \App\Helpers\Functions::format_as_currency($total_tax_2);
		$cart_total = \App\Helpers\Functions::format_as_currency($cart_total);
		Log::debug(['[12] $cart_total = \App\Helpers\Functions::format_as_currency($cart_total);', $cart_total]);

		if ($checkout) {
			$adminemailitems = "";
			$adminemailitems .= $_LANG["ordertotalduetoday"] . ": " . new \App\Helpers\FormatterPrice($cart_total, $currency);
			if ($promotioncode && (isset($promo_data["promoapplied"]) && $promo_data["promoapplied"])) {
				\App\Models\Promotion::where('code', $promotioncode)->increment('uses');
				$promo_recurring = $promo_data["recurring"] ? "Recurring" : "One Time";
				\App\Models\Order::where('id', $orderid)->update(["promocode" => $promo_data["code"], "promotype" => $promo_recurring . " " . $promo_data["type"], "promovalue" => $promo_data["value"]]);
			}
			if (session("cart.ns1")) {
				$ordernameservers = session("cart.ns1") . "," . session("cart.ns2");
				if (session("cart.ns3")) {
					$ordernameservers .= "," . session("cart.ns3");
				}
				if (session("cart.ns4")) {
					$ordernameservers .= "," . session("cart.ns4");
				}
				if (session("cart.ns5")) {
					$ordernameservers .= "," . session("cart.ns5");
				}
			}
			$domaineppcodes = count($domaineppcodes) ? (new \App\Helpers\Pwd())->safe_serialize($domaineppcodes) : "";
			$orderdata = [];
			if (is_array(session("cart.bundle"))) {
				foreach (session("cart.bundle") as $bvals) {
					$orderdata["bundleids"][] = $bvals["bid"];
				}
			}

			\App\Models\Order::where(["id" => $orderid])->update(
				["amount" => $cart_total, "nameservers" => $ordernameservers ?? "", "transfersecret" => $domaineppcodes, "renewals" => substr($orderrenewals, 0, -1), "orderdata" => (new \App\Helpers\Pwd())->safe_serialize($orderdata)]);
			$invoiceid = 0;

			if (!session("cart.geninvoicedisabled")) {
				if (!$userid) {
					$errMsg = "An error occurred";
					if (Application::isApiRequest()) {
						$apiresults = array("result" => "error", "message" => $errMsg);
						return $apiresults;
					}
					throw new \App\Exceptions\Fatal($errMsg);
				}

				// CHECKOUT: Step 5 create invoice
				$invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userid, true, "", array("products" => $orderproductids, "addons" => $orderaddonids, "domains" => $orderdomainids));
                

				// if ($CONFIG["OrderDaysGrace"]) {
				// 	$new_time = mktime(0, 0, 0, date("m"), date("d") + $CONFIG["OrderDaysGrace"], date("Y"));
				// 	$duedate = date("Y-m-d", $new_time);
				// 	\App\Models\Invoice::where(array("id" => $invoiceid))->update(array("duedate" => $duedate));
				// }
				// if (!$CONFIG["NoInvoiceEmailOnOrder"]) {
				// 	$invoiceArr = array("source" => "autogen", "user" => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : "system", "invoiceid" => $invoiceid);
				// 	\App\Helpers\Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
				// 	\App\Helpers\Functions::sendMessage("Invoice Created", $invoiceid);
                //        \Log::info('Isi invoiceArr sebelum memicu event:', $invoiceArr);
				// }
                if ($invoiceid > 0) { // Pastikan invoiceid valid
                    if ($CONFIG["OrderDaysGrace"]) {
                        $new_time = mktime(0, 0, 0, date("m"), date("d") + $CONFIG["OrderDaysGrace"], date("Y"));
                        $duedate = date("Y-m-d", $new_time);
                        \App\Models\Invoice::where(array("id" => $invoiceid))->update(array("duedate" => $duedate));
                    }
                    if (!$CONFIG["NoInvoiceEmailOnOrder"]) {
                        $invoiceArr = array("source" => "autogen", "user" => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : "system", "invoiceid" => $invoiceid);
                        \App\Helpers\Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
                        \App\Helpers\Functions::sendMessage("Invoice Created", $invoiceid);
                        // \Log::info('Isi invoiceArr sebelum memicu event:', $invoiceArr);
                    }
                } else {
                    \Log::error('Gagal membuat invoice, invoiceid tidak valid.');
                }
			}
			if ($invoiceid) {
				\App\Models\Order::where('id', $orderid)->update(array("invoiceid" => $invoiceid));
				$result = \App\Models\Invoice::where(array("id" => $invoiceid));
				$data = $result;
				$status = $data->value("status") ?? "";
				if ($status == "Paid") {
					if ($orderid) {
						\App\Helpers\Hooks::run_hook("OrderPaid", array("orderId" => $orderid, "userId" => $userid, "invoiceId" => $invoiceid));
					}
					$invoiceid = "";
				}
			}
			if (!Auth::guard('admin')->check()) {
				if (CookieHelper::get('AffiliateID')) {
					$result = Affiliate::select("clientid")->where('id', (int) CookieHelper::get("AffiliateID"));
					$data = $result;
					$clientid = $data->value("clientid");
					$uID = $auth ? $auth->id : 0;
					if ($clientid && $uID != $clientid) {
						foreach ($orderproductids as $orderproductid) {
							AffiliateAccount::insert(array("affiliateid" => (int) CookieHelper::get("AffiliateID"), "relid" => $orderproductid));
						}
					}
				}
				if (CookieHelper::get('LinkID')) {
					\App\Models\Link::find(CookieHelper::get('LinkID'))->increment("conversions", 1);
				}
			}

			$result = \App\Models\Client::selectRaw("firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber, ip, host")->where('id', $userid)->first();
			$data = $result->toArray();
			
			list(
				'firstname' => $firstname,
					'lastname' => $lastname,
					'companyname' => $companyname,
					'email' => $email,
					'address1' => $address1,
					'address2' => $address2,
					'city' => $city,
					'state' => $state,
					'postcode' => $postcode,
					'country' => $country,
					'phonenumber' => $phonenumber,
					'ip' => $ip,
					'host' => $host
				) = $data;

			$customfields = \App\Helpers\Customfield::getCustomFields("client", "", $userid, "", true);
			$clientcustomfields = "";
			foreach ($customfields as $customfield) {
				$clientcustomfields .= (string) $customfield["name"] . ": " . $customfield["value"] . "<br />\n";
			}

			$result = \App\Models\Paymentgateway::where(["gateway" => $paymentmethod, "setting" => "name"]);
			
			$data = $result;
			$nicegatewayname = $data->value("value") ?? "";
			\App\Helpers\Functions::sendAdminMessage("New Order Notification", [
				"order_id" => $orderid,
				"order_number" => $order_number,
				"order_date" => (new \App\Helpers\Functions())->fromMySQLDate(date("Y-m-d H:i:s"), true),
				"invoice_id" => $invoiceid,
				"order_payment_method" => $nicegatewayname,
				"order_total" => new \App\Helpers\FormatterPrice($cart_total, $currency),
				"client_id" => $userid,
				"client_first_name" => $firstname,
				"client_last_name" => $lastname,
				"client_email" => $email,
				"client_company_name" => $companyname,
				"client_address1" => $address1,
				"client_address2" => $address2,
				"client_city" => $city,
				"client_state" => $state,
				"client_postcode" => $postcode,
				"client_country" => $country,
				"client_phonenumber" => $phonenumber,
				"client_customfields" => $clientcustomfields,
				"order_items" => $adminemailitems,
				"order_notes" => nl2br($ordernotes),
				"client_ip" => $ip,
				"client_hostname" => $host
			], "account");
			if (!session("cart.orderconfdisabled")) {
				\App\Helpers\Functions::sendMessage("Order Confirmation", $userid, [
					"order_id" => $orderid,
					"order_number" => $order_number,
					"order_details" => $adminemailitems
				]);
			}
			session()->put('cart', []);
			session()->put('orderdetails', [
				"OrderID" => $orderid,
				"OrderNumber" => $order_number,
				"ServiceIDs" => $orderproductids,
				"DomainIDs" => $orderdomainids,
				"AddonIDs" => $orderaddonids,
				"UpgradeIDs" => $orderUpgradeIds,
				"RenewalIDs" => $orderrenewalids,
				"PaymentMethod" => $paymentmethod,
				"InvoiceID" => $invoiceid,
				"TotalDue" => $cart_total,
				"Products" => $orderproductids,
				"Domains" => $orderdomainids,
				"Addons" => $orderaddonids,
				"Renewals" => $orderrenewalids
			]);
			\App\Helpers\Hooks::run_hook("AfterShoppingCartCheckout", session('orderdetails'));
		}
		$total_recurringmonthly = $recurring_cycles_total["monthly"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["monthly"], $currency);
		Log::debug(['$recurring_cycles_total["monthly"]', $recurring_cycles_total["monthly"]]);

		$total_recurringquarterly = $recurring_cycles_total["quarterly"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["quarterly"], $currency);
		$total_recurringsemiannually = $recurring_cycles_total["semiannually"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["semiannually"], $currency);
		$total_recurringannually = $recurring_cycles_total["annually"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["annually"], $currency);
		$total_recurringbiennially = $recurring_cycles_total["biennially"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["biennially"], $currency);
		$total_recurringtriennially = $recurring_cycles_total["triennially"] <= 0 ? "" : new \App\Helpers\FormatterPrice($recurring_cycles_total["triennially"], $currency);

		$cartdata["bundlewarnings"] = $bundlewarnings ?? "";
		$cartdata["rawdiscount"] = $cart_discount;
		$cartdata["subtotal"] = new \App\Helpers\FormatterPrice($cart_subtotal, $currency);
		$cartdata["discount"] = new \App\Helpers\FormatterPrice($cart_discount, $currency);
		$cartdata["promotype"] = $promo_data["type"];
		$cartdata["promovalue"] = $promo_data["type"] == "Fixed Amount" || $promo_data["type"] == "Price Override" ? new \App\Helpers\FormatterPrice($promo_data["value"], $currency) : round($promo_data["value"], 2);
		$cartdata["promorecurring"] = $promo_data["recurring"] ? $_LANG["recurring"] : $_LANG["orderpaymenttermonetime"];
		$cartdata["taxrate"] = $rawtaxrate;
		$cartdata["taxrate2"] = $rawtaxrate2;
		$cartdata["taxname"] = $taxname;
		$cartdata["taxname2"] = $taxname2;
		$cartdata["taxtotal"] = new \App\Helpers\FormatterPrice($total_tax_1, $currency);
		$cartdata["taxtotal2"] = new \App\Helpers\FormatterPrice($total_tax_2, $currency);
		$cartdata["adjustments"] = $adjustments;
		$cartdata["adjustmentstotal"] = new \App\Helpers\FormatterPrice($cart_adjustments, $currency);
		$cartdata["rawtotal"] = $cart_total;
		$cartdata["total"] = new \App\Helpers\FormatterPrice($cart_total, $currency);
		$cartdata["totalrecurringmonthly"] = $total_recurringmonthly;
		$cartdata["totalrecurringquarterly"] = $total_recurringquarterly;
		$cartdata["totalrecurringsemiannually"] = $total_recurringsemiannually;
		$cartdata["totalrecurringannually"] = $total_recurringannually;
		$cartdata["totalrecurringbiennially"] = $total_recurringbiennially;
		$cartdata["totalrecurringtriennially"] = $total_recurringtriennially;
		$cartdata["recurring_cycles_total"] = $recurring_cycles_total;

		\App\Helpers\Hooks::run_hook("AfterCalculateCartTotals", $cartdata);
		return $cartdata;
	}

	public static function getAvailableOrderPaymentGatewaysOLD($forceAll = false)
	{
		$auth = Auth::guard("web")->user();
		$disabledGateways = [];
		$cartSession = Session::get("cart");
		if (isset($cartSession["products"])) {
			foreach ($cartSession["products"] as $values) {
				$groupDisabled = DB::table("tblproductgroups")->join("tblproducts", "tblproducts.gid", "=", "tblproductgroups.id")->where("tblproducts.id", "=", $values["pid"])->first(array("disabledgateways"));
				if ($groupDisabled) {
					$disabledGateways = array_merge(explode(",", $groupDisabled->disabledgateways), $disabledGateways);
				}
			}
		}
		$userId = $auth ? $auth->id : NULL;
		$gatewaysList = \App\Helpers\Gateway::showPaymentGatewaysList(array_unique($disabledGateways), $userId, $forceAll);
		foreach ($gatewaysList as $module => $values) {
			$module = $module;
			$gatewaysList[$module]["payment_type"] = "Invoices";
			if (($values["type"] == "CC" || $values["type"] == "OfflineCC") && !\Module::find($module)) {
				$errorMessage = "Invalid Gateway Module Name";
				if (Application::isApiRequest()) {
					// return ResponseAPI::Error([
					//   'message' => $errorMessage,
					// ]);
					$apiResults = array("result" => "error", "message" => $errorMessage);
					return $apiResults;
				}
				throw new \App\Exceptions\Fatal($errorMessage);
			}
			$gatewaysList[$module]["payment_type"] = "CreditCard";
			$gatewayInterface = \App\Module\Gateway::factory($module);
			switch ($gatewayInterface->getWorkflowType()) {
				case \App\Module\Gateway::WORKFLOW_ASSISTED:
					$gatewaysList[$module]["payment_type"] = "RemoteCreditCard";
					$gatewaysList[$module]["show_local_cards"] = false;
					break;
				case \App\Module\Gateway::WORKFLOW_REMOTE:
				case \App\Module\Gateway::WORKFLOW_TOKEN:
					$gatewaysList[$module]["payment_type"] = "RemoteCreditCard";
					break;
				case \App\Module\Gateway::WORKFLOW_MERCHANT:
					$gatewaysList[$module]["payment_type"] = "CreditCard";
					break;
				case \App\Module\Gateway::WORKFLOW_NOLOCALCARDINPUT:
				case \App\Module\Gateway::WORKFLOW_THIRDPARTY:
					$gatewaysList[$module]["payment_type"] = "Invoices";
					$gatewaysList[$module]["show_local_cards"] = false;
					$gatewaysList[$module]["type"] = "Invoices";
					break;
			}
		}
		return $gatewaysList;
	}

	public static function getAvailableOrderPaymentGateways($forceAll = false)
	{
		$auth = Auth::guard("web")->user();
		$disabledGateways = [];
		$cartSession = Session::get("cart");
		if (isset($cartSession["products"])) {
			foreach ($cartSession["products"] as $values) {
				$groupDisabled = DB::table("tblproductgroups")->join("tblproducts", "tblproducts.gid", "=", "tblproductgroups.id")->where("tblproducts.id", "=", $values["pid"])->first(array("disabledgateways"));
				$disabledGateways = array_merge(explode(",", $groupDisabled->disabledgateways), $disabledGateways);
			}
		}
		$userId = $auth ? $auth->id : NULL;
		$gatewaysList = \App\Helpers\Gateway::showPaymentGatewaysList(array_unique($disabledGateways), $userId, $forceAll);
		foreach ($gatewaysList as $module => $values) {
			$gatewaysList[$module]["payment_type"] = "Invoices";
			if (($values["type"] == "CC" || $values["type"] == "OfflineCC") && !\Module::find($module)) {
				$errorMessage = "Invalid Gateway Module Name";
				if (Application::isApiRequest()) {
					$apiResults = array("result" => "error", "message" => $errorMessage);
					return $apiResults;
				}
				throw new \App\Exceptions\Fatal($errorMessage);
			}
			if (\Module::find($module)) {
				$gatewaysList[$module]["payment_type"] = "CreditCard";
				$gatewayInterface = \App\Module\Gateway::factory($module);
				$gatewaysList[$module]["payment_type"] = "Invoices";
				$gatewaysList[$module]["show_local_cards"] = true;
				switch ($gatewayInterface->getWorkflowType()) {
					case \App\Module\Gateway::WORKFLOW_ASSISTED:
						$gatewaysList[$module]["payment_type"] = "RemoteCreditCard";
						$gatewaysList[$module]["show_local_cards"] = false;
						break;
					case \App\Module\Gateway::WORKFLOW_REMOTE:
					case \App\Module\Gateway::WORKFLOW_TOKEN:
						$gatewaysList[$module]["payment_type"] = "RemoteCreditCard";
						break;
					case \App\Module\Gateway::WORKFLOW_MERCHANT:
						$gatewaysList[$module]["payment_type"] = "CreditCard";
						break;
					case \App\Module\Gateway::WORKFLOW_NOLOCALCARDINPUT:
					case \App\Module\Gateway::WORKFLOW_THIRDPARTY:
						$gatewaysList[$module]["payment_type"] = "Invoices";
						$gatewaysList[$module]["show_local_cards"] = false;
						$gatewaysList[$module]["type"] = "Invoices";
						break;
				}
			}
		}

		$gatewaysList = self::filterGatewaysByRestrictionPeriod($gatewaysList);

		return $gatewaysList;
	}

	/**
	 * Filter daftar gateway: 13–29 Maret hanya tampilkan Bank Transfer (BCA/Mandiri/Maybank), PayPal, Stripe, Wise.
	 * Dipakai di portal checkout dan admin Add Order.
	 *
	 * @param array $gateways [ sysname => value, ... ]
	 * @return array
	 */
	public static function filterGatewaysByRestrictionPeriod(array $gateways)
	{
		$allowedDuringRestriction = ['banktransfer', 'mandiritransfer', 'maybank', 'maybanktransfer', 'paypal', 'paypalexpress', 'stripe', 'wise'];
		$now = now();
		$start = $now->copy()->month(3)->day(13)->startOfDay();
		$end = $now->copy()->month(4)->day(7)->endOfDay();
		if (!$now->between($start, $end)) {
			return $gateways;
		}
		$allowed = array_flip(array_map('strtolower', $allowedDuringRestriction));
		$filtered = [];
		foreach ($gateways as $sysname => $value) {
			if (isset($allowed[strtolower((string) $sysname)])) {
				$filtered[$sysname] = $value;
			}
		}
		return $filtered;
	}

	public static function getPricingInfo($pid, $inclconfigops = false, $upgrade = false)
	{
		global $CONFIG, $_LANG, $currency;

		// Get product details
		$product = \App\Models\Product::findOrFail($pid);
		$data = $product->toArray();
		$paytype = $data["paytype"];
		$freedomain = $data["freedomain"];
		$freedomainpaymentterms = $data["freedomainpaymentterms"];

		// Ensure currency is set
		if (!isset($currency["id"])) {
			$currency = \App\Helpers\Format::getCurrency();
		}

		// Get pricing data
		$result = \App\Models\Pricing::where('type', 'product')
			->where('currency', $currency["id"])
			->where('relid', $pid);
		$data = $result;

		// Setup fees
		$msetupfee = $data->value("msetupfee") ?? 0;
		$qsetupfee = $data->value("qsetupfee") ?? 0;
		$ssetupfee = $data->value("ssetupfee") ?? 0;
		$asetupfee = $data->value("asetupfee") ?? 0;
		$bsetupfee = $data->value("bsetupfee") ?? 0;
		$tsetupfee = $data->value("tsetupfee") ?? 0;

		// Recurring prices
		$monthly = $data->value("monthly") ?? 0;
		$quarterly = $data->value("quarterly") ?? 0;
		$semiannually = $data->value("semiannually") ?? 0;
		$annually = $data->value("annually") ?? 0;
		$biennially = $data->value("biennially") ?? 0;
		$triennially = $data->value("triennially") ?? 0;

		// Initialize variables
		$configoptions = new \App\Helpers\ProductConfigOptions();
		$freedomainpaymentterms = explode(",", $freedomainpaymentterms);
		$monthlypricingbreakdown = Cfg::get("ProductMonthlyPricingBreakdown");
		$minprice = 0;
		$setupFee = 0;
		$mincycle = "";
		$pricing = [];

		// Handle pricing based on payment type
		if ($paytype == "free") {
			$pricing["type"] = $mincycle = "free";
		} else if ($paytype == "onetime") {
			if ($inclconfigops) {
				$msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
				$monthly += $configoptions->getBasePrice($pid, "monthly");
			}
			$minprice = $monthly;
			$setupFee = $msetupfee;
			$pricing["type"] = $mincycle = "onetime";
			$pricing["onetime"] = new \App\Helpers\FormatterPrice($monthly, $currency);

			if ($msetupfee != "0.00") {
				$pricing["onetime"] .= " + " . new \App\Helpers\FormatterPrice($msetupfee, $currency) . " " . $_LANG["ordersetupfee"];
			}
			if (in_array("onetime", $freedomainpaymentterms) && $freedomain && !$upgrade) {
				$pricing["onetime"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
			}
		} else if ($paytype == "recurring") {
			$pricing["type"] = "recurring";

			// Monthly pricing
			if ($monthly >= 0) {
				if ($inclconfigops) {
					$msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
					$monthly += $configoptions->getBasePrice($pid, "monthly");
				}
				if (!$mincycle) {
					$minprice = $monthly;
					$setupFee = $msetupfee;
					$mincycle = "monthly";
					$minMonths = 1;
				}

				if ($monthlypricingbreakdown) {
					$pricing["monthly"] = Lang::get("admin.orderpaymentterm1month") . " - " . new \App\Helpers\FormatterPrice($monthly, $currency);
				} else {
					$pricing["monthly"] = new \App\Helpers\FormatterPrice($monthly, $currency) . " " . Lang::get('admin.orderpaymenttermmonthly');
				}

				if ($msetupfee != "0.00") {
					$pricing["monthly"] .= " + " . new \App\Helpers\FormatterPrice($msetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("monthly", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["monthly"] .= " (" . Lang::get("orderfreedomainonly") . ")";
				}
			}

			// Quarterly pricing
			if ($quarterly >= 0) {
				if ($inclconfigops) {
					$qsetupfee += $configoptions->getBasePrice($pid, "qsetupfee");
					$quarterly += $configoptions->getBasePrice($pid, "quarterly");
				}
				if (!$mincycle) {
					$minprice = $monthlypricingbreakdown ? $quarterly / 3 : $quarterly;
					$setupFee = $qsetupfee;
					$mincycle = "quarterly";
					$minMonths = 3;
				}

				if ($monthlypricingbreakdown) {
					$pricing["quarterly"] = Lang::get("admin.orderpaymentterm3month") . " - " . new \App\Helpers\FormatterPrice($quarterly / 3, $currency);
				} else {
					$pricing["quarterly"] = new \App\Helpers\FormatterPrice($quarterly, $currency) . " " . Lang::get("admin.orderpaymenttermquarterly");
				}

				if ($qsetupfee != "0.00") {
					$pricing["quarterly"] .= " + " . new \App\Helpers\FormatterPrice($qsetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("quarterly", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["quarterly"] .= " (" . Lang::get("admin.orderfreedomainonly") . ")";
				}
			}

			// Semiannually pricing
			if ($semiannually >= 0) {
				if ($inclconfigops) {
					$ssetupfee += $configoptions->getBasePrice($pid, "ssetupfee");
					$semiannually += $configoptions->getBasePrice($pid, "semiannually");
				}
				if (!$mincycle) {
					$minprice = $monthlypricingbreakdown ? $semiannually / 6 : $semiannually;
					$setupFee = $ssetupfee;
					$mincycle = "semiannually";
					$minMonths = 6;
				}

				if ($monthlypricingbreakdown) {
					$pricing["semiannually"] = Lang::get("admin.orderpaymentterm6month") . " - " . new \App\Helpers\FormatterPrice($semiannually / 6, $currency);
				} else {
					$pricing["semiannually"] = new \App\Helpers\FormatterPrice($semiannually, $currency) . " " . Lang::get("admin.orderpaymenttermsemiannually");
				}

				if ($ssetupfee != "0.00") {
					$pricing["semiannually"] .= " + " . new \App\Helpers\FormatterPrice($ssetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("semiannually", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["semiannually"] .= " (" . Lang::get("admin.orderfreedomainonly") . ")";
				}
			}

			// Annually pricing
			if ($annually >= 0) {
				if ($inclconfigops) {
					$asetupfee += $configoptions->getBasePrice($pid, "asetupfee");
					$annually += $configoptions->getBasePrice($pid, "annually");
				}
				if (!$mincycle) {
					$minprice = $monthlypricingbreakdown ? $annually / 12 : $annually;
					$setupFee = $asetupfee;
					$mincycle = "annually";
					$minMonths = 12;
				}

				if ($monthlypricingbreakdown) {
					$pricing["annually"] = Lang::get("admin.orderpaymentterm12month") . " - " . new \App\Helpers\FormatterPrice($annually / 12, $currency);
				} else {
					$pricing["annually"] = new \App\Helpers\FormatterPrice($annually, $currency) . " " . Lang::get("admin.orderpaymenttermannually");
				}

				if ($asetupfee != "0.00") {
					$pricing["annually"] .= " + " . new \App\Helpers\FormatterPrice($asetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("annually", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["annually"] .= " (" . Lang::get("admin.orderfreedomainonly") . ")";
				}
			}

			// Biennially pricing
			if ($biennially >= 0) {
				if ($inclconfigops) {
					$bsetupfee += $configoptions->getBasePrice($pid, "bsetupfee");
					$biennially += $configoptions->getBasePrice($pid, "biennially");
				}
				if (!$mincycle) {
					$minprice = $monthlypricingbreakdown ? $biennially / 24 : $biennially;
					$setupFee = $bsetupfee;
					$mincycle = "biennially";
					$minMonths = 24;
				}

				if ($monthlypricingbreakdown) {
					$pricing["biennially"] = Lang::get("admin.orderpaymentterm24month") . " - " . new \App\Helpers\FormatterPrice($biennially / 24, $currency);
				} else {
					$pricing["biennially"] = new \App\Helpers\FormatterPrice($biennially, $currency) . " " . Lang::get("admin.orderpaymenttermbiennially");
				}

				if ($bsetupfee != "0.00") {
					$pricing["biennially"] .= " + " . new \App\Helpers\FormatterPrice($bsetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("biennially", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["biennially"] .= " (" . Lang::get("admin.orderfreedomainonly") . ")";
				}
			}

			// Triennially pricing
			if ($triennially >= 0) {
				if ($inclconfigops) {
					$tsetupfee += $configoptions->getBasePrice($pid, "tsetupfee");
					$triennially += $configoptions->getBasePrice($pid, "triennially");
				}
				if (!$mincycle) {
					$minprice = $monthlypricingbreakdown ? $triennially / 36 : $triennially;
					$setupFee = $tsetupfee;
					$mincycle = "triennially";
					$minMonths = 36;
				}

				if ($monthlypricingbreakdown) {
					$pricing["triennially"] = Lang::get("admin.orderpaymentterm36month") . " - " . new \App\Helpers\FormatterPrice($triennially / 36, $currency);
				} else {
					$pricing["triennially"] = new \App\Helpers\FormatterPrice($triennially, $currency) . " " . Lang::get("admin.orderpaymenttermtriennially");
				}

				if ($tsetupfee != "0.00") {
					$pricing["triennially"] .= " + " . new \App\Helpers\FormatterPrice($tsetupfee, $currency) . " " . Lang::get("admin.ordersetupfee");
				}
				if (in_array("triennially", $freedomainpaymentterms) && $freedomain && !$upgrade) {
					$pricing["triennially"] .= " (" . Lang::get("admin.orderfreedomainonly") . ")";
				}
			}
		}

		// Set config options and build cycles array
		$pricing["hasconfigoptions"] = $configoptions->hasConfigOptions($pid);

		if (isset($pricing["onetime"])) {
			$pricing["cycles"]["onetime"] = $pricing["onetime"];
		}
		if (isset($pricing["monthly"])) {
			$pricing["cycles"]["monthly"] = $pricing["monthly"];
		}
		if (isset($pricing["quarterly"])) {
			$pricing["cycles"]["quarterly"] = $pricing["quarterly"];
		}
		if (isset($pricing["semiannually"])) {
			$pricing["cycles"]["semiannually"] = $pricing["semiannually"];
		}
		if (isset($pricing["annually"])) {
			$pricing["cycles"]["annually"] = $pricing["annually"];
		}
		if (isset($pricing["biennially"])) {
			$pricing["cycles"]["biennially"] = $pricing["biennially"];
		}
		if (isset($pricing["triennially"])) {
			$pricing["cycles"]["triennially"] = $pricing["triennially"];
		}

		// Set raw pricing data
		$pricing["rawpricing"] = [
			"msetupfee" => \App\Helpers\Format::AsCurrency($msetupfee),
			"qsetupfee" => \App\Helpers\Format::AsCurrency($qsetupfee),
			"ssetupfee" => \App\Helpers\Format::AsCurrency($ssetupfee),
			"asetupfee" => \App\Helpers\Format::AsCurrency($asetupfee),
			"bsetupfee" => \App\Helpers\Format::AsCurrency($bsetupfee),
			"tsetupfee" => \App\Helpers\Format::AsCurrency($tsetupfee),
			"monthly" => \App\Helpers\Format::AsCurrency($monthly),
			"quarterly" => \App\Helpers\Format::AsCurrency($quarterly),
			"semiannually" => \App\Helpers\Format::AsCurrency($semiannually),
			"annually" => \App\Helpers\Format::AsCurrency($annually),
			"biennially" => \App\Helpers\Format::AsCurrency($biennially),
			"triennially" => \App\Helpers\Format::AsCurrency($triennially)
		];

		// Set minimum price info
		$pricing["minprice"] = [
			"price" => new \App\Helpers\FormatterPrice($minprice, $currency),
			"setupFee" => $setupFee > 0 ? new \App\Helpers\FormatterPrice($setupFee, $currency) : 0,
			"cycle" => $monthlypricingbreakdown && $paytype == "recurring" ? "monthly" : $mincycle,
			"simple" => (new \App\Helpers\FormatterPrice($minprice, $currency))->toPrefixed()
		];

		// Add cycle text if applicable
		if (isset($minMonths)) {
			switch ($minMonths) {
				case 3:
					$langVar = "shoppingCartProductPerMonth";
					$count = "3 ";
					break;
				case 6:
					$langVar = "shoppingCartProductPerMonth";
					$count = "6 ";
					break;
				case 12:
					$langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
					$count = "";
					break;
				case 24:
					$langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
					$count = "2 ";
					break;
				case 36:
					$langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
					$count = "3 ";
					break;
				default:
					$langVar = "shoppingCartProductPerMonth";
					$count = "";
			}

			$pricing["minprice"]["cycleText"] = Lang::get($langVar, [
				"count" => $count,
				"price" => $pricing["minprice"]["simple"]
			]);

			$pricing["minprice"]["cycleTextWithCurrency"] = Lang::get($langVar, [
				"count" => $count,
				"price" => $pricing["minprice"]["price"]
			]);
		}

		return $pricing;
	}

	public static function getProductInfo($pid)
	{
		$result = DB::table('tblproducts')
			->select("tblproducts.id", "tblproducts.name", "tblproducts.description", "tblproducts.gid", "tblproducts.type", "tblproductgroups.id AS group_id", "tblproductgroups.name as group_name", "tblproducts.freedomain", "tblproducts.freedomainpaymentterms", "tblproducts.freedomaintlds", "tblproducts.stockcontrol", "tblproducts.qty")
			->where("tblproducts.id", $pid)
			->join("tblproductgroups", "tblproductgroups.id", "=", "tblproducts.gid")
			->first();
		$data = collect($result)->toArray();
		$productinfo = [];
		$productinfo["pid"] = $data["id"];
		$productinfo["gid"] = $data["gid"];
		$productinfo["type"] = $data["type"];
		$productinfo["groupname"] = \App\Models\Productgroup::getGroupName($data["group_id"], $data["group_name"]);
		$productinfo["name"] = \App\Models\Product::getProductName($data["id"], $data["name"]);
		$productinfo["description"] = nl2br(\App\Models\Product::getProductDescription($data["id"]), $data["description"]);
		$productinfo["freedomain"] = $data["freedomain"];
		$productinfo["freedomainpaymentterms"] = explode(",", $data["freedomainpaymentterms"]);
		$productinfo["freedomaintlds"] = explode(",", $data["freedomaintlds"]);
		$productinfo["qty"] = $data["stockcontrol"] ? $data["qty"] : "";
		return $productinfo;
	}

	public static function CalcPromoDiscount($pid, $cycle, $fpamount, $recamount, $setupfee = 0)
	{
		global $promo_data, $currency;

		$id = $promo_data["id"];
		$promotionCode = $promo_data["code"];
		if (!$id) {
			return false;
		}

		$anyPromotionPermission = false;
		$auth = Auth::guard("web")->user();

		if (Auth::guard('admin')->check() && !Application::isClientAreaRequest()) {
			$anyPromotionPermission = Auth::guard('admin')->user()->hasPermissionTo('Use Any Promotion Code on Order');
		}

		if (!$anyPromotionPermission) {
			if ($promo_data["newsignups"] && $auth) {
				$previousOrders = \App\Models\Order::where('userid', $auth->id)->count();
				if ($previousOrders >= 2) {
					return false;
				}
			}

			if ($promo_data["existingclient"]) {
				$orderCount = \App\Models\Order::where('userid', $auth->id)->where('status', 'Active')->count();
				if ($orderCount < 1) {
					return false;
				}
			}

			if ($promo_data["onceperclient"]) {
				$orderCount = \App\Models\Order::where('userid', $auth->id)
					->where('promocode', $promotionCode)
					->whereIn('status', ["Pending", "Active"])
					->count();
				if ($orderCount > 0) {
					return false;
				}
			}

			if ($promo_data["applyonce"] && ($promo_data["promoapplied"] ?? false)) {
				return false;
			}

			if (!in_array($pid, explode(",", $promo_data["appliesto"]))) {
				return false;
			}

			if ($promo_data["expirationdate"] && $promo_data["expirationdate"] != "0000-00-00") {
				$validUntil = str_replace("-", "", $promo_data["expirationdate"]);
				$todaysDate = date("Ymd");
				if ($validUntil < $todaysDate) {
					return false;
				}
			}

			if ($promo_data["cycles"]) {
				if (!in_array($cycle, explode(",", $promo_data["cycles"]))) {
					return false;
				}
			}

			if ($promo_data["maxuses"] && $promo_data["maxuses"] <= $promo_data["uses"]) {
				return false;
			}

			if ($promo_data["requires"]) {
				$requires = explode(",", $promo_data["requires"]);
				$hasRequired = false;

				foreach (['products', 'addons', 'domains'] as $type) {
					if (is_array(session("cart.$type"))) {
						foreach (session("cart.$type") as $values) {
							if ($type === 'products' && in_array($values["pid"], $requires)) {
								$hasRequired = true;
							}
							if ($type === 'addons' && in_array("A" . $values["id"], $requires)) {
								$hasRequired = true;
							}
							if ($type === 'domains') {
								$tld = explode(".", $values["domain"], 2)[1];
								if (in_array("D." . $tld, $requires)) {
									$hasRequired = true;
								}
							}
						}
					}
				}

				if (!$hasRequired && $promo_data["requiresexisting"]) {
					$requiredProducts = $requiredAddons = [];
					$requiredDomains = "";

					foreach ($requires as $v) {
						if (substr($v, 0, 1) == "A") {
							$requiredAddons[] = substr($v, 1);
						} elseif (substr($v, 0, 1) == "D") {
							$requiredDomains .= "domain LIKE '%" . substr($v, 1) . "' OR ";
						} else {
							$requiredProducts[] = $v;
						}
					}

					if (count($requiredProducts)) {
						$data = \App\Models\Hosting::where('userid', $auth->id)
							->whereIn('packageid', $requiredProducts)
							->where('domainstatus', 'Active')
							->count();
						if ($data) {
							$hasRequired = true;
						}
					}

					if (count($requiredAddons)) {
						$data = \App\Models\Hostingaddon::where('tblhosting.userid', $auth->id)
							->where('status', 'Active')
							->whereIn('addonid', $requiredAddons)
							->join("tblhosting", "tblhosting.id", "=", "tblhostingaddons.hostingid")
							->count();
						if ($data) {
							$hasRequired = true;
						}
					}

					if ($requiredDomains) {
						$data = \App\Models\Domain::where('userid', $auth->id)
							->where('status', 'Active')
							->whereRaw(substr($requiredDomains, 0, -4))
							->count();
						if ($data) {
							$hasRequired = true;
						}
					}
				}

				if (!$hasRequired) {
					return false;
				}
			}
		}

		// Calculate discounts
		$type = $promo_data["type"];
		$value = $promo_data["value"];
		$onetimediscount = 0;

		if ($type == "Percentage") {
			$onetimediscount = $fpamount * $value / 100;
		} elseif ($type == "Fixed Amount") {
			if ($currency["id"] != 1) {
				$value = \App\Helpers\Format::ConvertCurrency($value, 1, $currency["id"]);
			}
			$onetimediscount = min($fpamount, $value);
		} elseif ($type == "Price Override") {
			if ($currency["id"] != 1) {
				$promo_data["value"] = \App\Helpers\Format::ConvertCurrency($promo_data["value"], 1, $currency["id"]);
			}
			$onetimediscount = $fpamount - $promo_data["priceoverride"];
		} elseif ($type == "Free Setup") {
			$onetimediscount = $setupfee;
			$promo_data["value"] += $setupfee;
		}

		$recurringdiscount = 0;
		if ($promo_data["recurring"]) {
			if ($type == "Percentage") {
				$recurringdiscount = $recamount * $value / 100;
			} elseif ($type == "Fixed Amount") {
				$recurringdiscount = min($recamount, $value);
			} elseif ($type == "Price Override") {
				$recurringdiscount = $recamount - $promo_data["priceoverride"];
			}
		}

		$onetimediscount = round($onetimediscount, 2);
		$recurringdiscount = round($recurringdiscount, 2);
		$promo_data["promoapplied"] = true;

		return [
			"onetimediscount" => $onetimediscount,
			"recurringdiscount" => $recurringdiscount,
			"applyonce" => $promo_data["applyonce"]
		];
	}

	public static function getStatuses()
	{
		$statuses = [];
		$result = \App\Models\Orderstatus::select("title", "color")->orderBy("sortorder", "ASC")->get()->toArray();

		foreach ($result as $data) {
			$statuses[$data["title"]] = "<span style=\"color:" . $data["color"] . "\">" . $data["title"] . "</span>";
		}

		self::$statusoutputs = $statuses;

		return $statuses;
	}

	public static function formatStatus($status)
	{
		if (!self::$statusoutputs) {
			self::getStatuses();
		}

		return array_key_exists($status, self::$statusoutputs) ? self::$statusoutputs[$status] : $status;
	}

	public static function getFormatedPaymentStatus($invoiceid, $invoicestatus)
	{
		$paymentstatus = "";

		if ($invoiceid == "0") {
			// No invoice due
			$paymentstatus = "<span class=\"textgreen\">" . __("admin.ordersnoinvoicedue") . "</span>";
		} elseif (!$invoicestatus) {
			// Invoice deleted
			$paymentstatus = "<span class=\"textred\">Invoice Deleted</span>";
		} elseif ($invoicestatus == "Paid") {
			// Invoice paid
			$paymentstatus = "<span class=\"textgreen\">" . __("admin.statuscomplete") . "</span>";
		} elseif ($invoicestatus == "Unpaid") {
			// Invoice unpaid
			$paymentstatus = "<span class=\"textred\">" . __("admin.statusincomplete") . "</span>";
		} else {
			// Other invoice status
			$paymentstatus = \App\Helpers\Invoice::getInvoiceStatusColour($invoicestatus, false);
		}

		return $paymentstatus;
	}

	public static function CancelRefundOrder($orderid)
	{
		$orderid = (int) $orderid;
		$result = Order::select("invoiceid")->find($orderid);
		if (!$result) {
			return "noorder";
		}

		$data = $result->toArray();
		$invoiceid = $data["invoiceid"];
		if ($invoiceid) {
			$result = Invoice::select("status")->find($invoiceid);
			if (!$result) {
				return "noinvoice";
			}

			$data = $result->toArray();
			$invoicestatus = $data["status"];
			if ($invoicestatus == "Paid") {
				$result = Account::select("id")->where("invoiceid", $invoiceid)->first();
				$data = $result->toArray();
				$transid = $data["id"];
				$gatewayresult = InvoiceHelper::refundInvoicePayment($transid, "", true);
				if ($gatewayresult == "manual") {
					return "manual";
				}
				if ($gatewayresult != "success") {
					return "refundfailed";
				}
				self::ChangeOrderStatus($orderid, "Refunded");
			} else {
				if ($invoicestatus == "Refunded") {
					return "alreadyrefunded";
				}
				return "notpaid";
			}
		} else {
			return "noinvoice";
		}
	}

	public static function SetPromoCode($promotioncode)
	{
		global $_LANG;
		$auth = Auth::guard('web')->user();
		session()->put("cart.promo", "");

		// Fetch promotion data
		$promotion = \App\Models\Promotion::where("code", $promotioncode)->first();
		if (!$promotion) {
			return Lang::get("client.ordercodenotfound");
		}

		$id = $promotion->id;
		$maxuses = $promotion->maxuses ?? 0;
		$uses = $promotion->uses ?? 0;
		$startdate = $promotion->startdate ?? "0000-00-00";
		$expiredate = $promotion->expirationdate ?? "0000-00-00";
		$newsignups = $promotion->newsignups;
		$existingclient = $promotion->existingclient;
		$onceperclient = $promotion->onceperclient;

		if ($startdate != "0000-00-00" && date("Ymd") < str_replace("-", "", $startdate)) {
			return Lang::get("client.orderpromoprestart");
		}

		if ($expiredate != "0000-00-00" && str_replace("-", "", $expiredate) < date("Ymd")) {
			return Lang::get("client.orderpromoexpired");
		}

		if ($maxuses > 0 && $maxuses <= $uses) {
			return Lang::get("client.orderpromomaxusesreached");
		}

		if ($newsignups && $auth) {
			$previousOrders = \App\Models\Order::where("userid", $auth->id)->count();
			if ($previousOrders > 0) {
				return Lang::get("client.promonewsignupsonly");
			}
		}

		if ($existingclient) {
			if ($auth) {
				$orderCount = \App\Models\Order::where("status", "Active")->where("userid", $auth->id)->count();
				if ($orderCount == 0) {
					return Lang::get("client.promoexistingclient");
				}
			} else {
				return Lang::get("client.promoexistingclient");
			}
		}

		if ($onceperclient && $auth) {
			$orderCount = \App\Models\Order::where("promocode", $promotioncode)
				->where("userid", $auth->id)
				->whereIn("status", ['Pending', 'Active'])
				->count();
			if ($orderCount > 0) {
				return Lang::get("client.promoonceperclient");
			}
		}

		session()->put("cart.promo", $promotioncode);

		return $promotioncode;
	}

	public function getPendingCount()
	{
		return (int) DB::table("tblorders")->join("tblorderstatuses", "tblorders.status", "=", "tblorderstatuses.title")->where("tblorderstatuses.showpending", "=", 1)->count("tblorders.id");
	}
}