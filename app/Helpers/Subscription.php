<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Hostingaddon;

// Import Package Class here
use App\Exceptions\Gateways\SubscriptionCancellationFailed;
use App\Exceptions\Gateways\SubscriptionCancellationNotSupported;
use App\Exceptions\Module\NotServicable;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Subscription
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public static function getInfo($relatedItem)
    {
        try {
			// TODO: Check $instanceType
            $instanceType = get_class($relatedItem);
            if (!in_array($instanceType, array("\\App\\Models\\Hostingaddon", "\\App\\Models\\Domain", "\\App\\Models\\Hosting"))) {
                throw new \InvalidArgumentException("Invalid Access Attempt");
            }

            $gatewayInterface = \App\Module\Gateway::factory($relatedItem->paymentGateway);
            if (!$gatewayInterface->functionExists("get_subscription_info") || !$relatedItem->subscriptionId) {
                throw new \InvalidArgumentException("Invalid Access Attempt");
            }

            $params = $gatewayInterface->getParams();
            $params["subscriptionId"] = $relatedItem->subscriptionId;
            $subscriptionInfo = $gatewayInterface->call("get_subscription_info", $params);
            if (!$subscriptionInfo) {
                throw new NotServicable("Invalid Response");
            }
			
            $subscriptionDetails = "";
            foreach ($subscriptionInfo as $key => $value) {
                $langId = "subscription." . str_replace("_", "", strtolower($key));
                $keyTranslation = \Lang::trans($langId);
                if (!$keyTranslation || $keyTranslation == $langId) {
                    $keyTranslation = $key;
                }
                $subscriptionDetails .= $keyTranslation . ": " . $value . "<br>";
            }

            // $response = array(
			// 	// TODO: Result view 
			// 	"body" => View::make("admin.client.profile.subscription-info", array(
			// 		"isActive" => strtolower($subscriptionInfo["Status"]) == "active", 
			// 		"subscriptionDetails" => $subscriptionDetails
			// 	))
			// );

			$response = array(
				"isActive" => strtolower($subscriptionInfo["Status"]) == "active", 
				"subscriptionDetails" => $subscriptionDetails,
			);
        } catch (\Exception $e) {
            // $response = array(
			// 	"body" => View::make("admin.client.profile.subscription-info", array("errorMsg" => $e->getMessage()))
			// );
			
			$response = array("errorMsg" => $e->getMessage());
        }

        return $response;
    }


	public static function cancel($relatedItem) {
		try {
			if ($relatedItem instanceof Hostingaddon) {
                $logMessage = " - Service Addon ID: " . $relatedItem->id;
            } else if ($relatedItem instanceof Domain) {
				$logMessage = " - Domain ID: " . $relatedItem->id;
			} else if ($relatedItem instanceof Hosting) {
				$logMessage = " - Service ID: " . $relatedItem->id;
			} else {
				throw new \InvalidArgumentException("Invalid Access Attempt");
			}

			$paymentMethod = $relatedItem->paymentGateway;
            $subscriptionId = $relatedItem->subscriptionId;
            $gatewayInterface = \App\Module\Gateway::factory($paymentMethod);
            if (!$gatewayInterface->functionExists("cancelSubscription")) {
                throw new SubscriptionCancellationNotSupported("Subscription Cancellation not Support by Gateway");
            }

			$params = array("subscriptionID" => $subscriptionId);
            $cancelResult = $gatewayInterface->call("cancelSubscription", $params);

			if (is_array($cancelResult) && $cancelResult["status"] == "success") {
                $relatedItem->subscriptionId = "";
                $relatedItem->save();
                if ($relatedItem instanceof Hostingaddon) {
                    Hostingaddon::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
                } else if ($relatedItem instanceof Domain) {
					Domain::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
				} else if ($relatedItem instanceof Hosting) {
					Hosting::where("subscriptionid", $subscriptionId)->where("paymentmethod", $paymentMethod)->where("userid", $relatedItem->clientId)->update(array("subscriptionid" => ""));
				}

                LogActivity::Save("Subscription Cancellation for ID " . $subscriptionId . " Successful" . $logMessage, $relatedItem->clientId);
                Gateway::logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Success");
                $response = array(
					"success" => true, 
					"successMsgTitle" => __("admin.success"), 
					"successMsg" => __("admin.servicescancelSubscriptionSuccess")
				);
            } else {
				LogActivity::Save("Subscription Cancellation for ID " . $subscriptionId . " Failed" . $logMessage, $relatedItem->clientId);
                Gateway::logTransaction($paymentMethod, $cancelResult["rawdata"], "Subscription Cancellation Failed");
                $errorMsg = "Subscription Cancellation Failed";
                if (isset($cancelResult["errorMsg"])) {
                    $errorMsg .= ": " . $cancelResult["errorMsg"];
                }

                throw new SubscriptionCancellationFailed($errorMsg);
			}
		} catch (\Exception $e) {
			$response = array(
				"errorMsg" => $e->getMessage(), 
				"errorMsgTitle" => __("admin.erroroccurred")
			);
		}

		return $response;
	}

}
