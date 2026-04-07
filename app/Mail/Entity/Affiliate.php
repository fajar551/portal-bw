<?php

namespace App\Mail\Entity;

use DB;

class Affiliate extends \App\Helpers\Emailer
{
    protected function getEntitySpecificMergeData($affiliateId)
    {
        $referralsTable = "";
        $affiliateData = DB::table("tblaffiliates")->find($affiliateId, array("id", "clientid", "visitors", "balance", "withdrawn"));
        if (is_null($affiliateData)) {
            throw new \Exception("Invalid affiliate id provided");
        }
        $id = $affiliateID = $affiliateData->id;
        $userID = $userid = $affiliateData->clientid;
        $visitors = $affiliateData->visitors;
        $balance = $affiliateData->balance;
        $withdrawn = $affiliateData->withdrawn;
        $this->setRecipient($userID);
        $balance = \App\Helpers\Format::formatCurrency($balance);
        $withdrawn = \App\Helpers\Format::formatCurrency($withdrawn);
        $titleSignupDate = \Lang::get("affiliatessignupdate");
        $titleProduct = \Lang::get("orderproduct");
        $titleAmount = \Lang::get("affiliatesamount");
        $titleBillingCycle = \Lang::get("orderbillingcycle");
        $titleCommission = \Lang::get("affiliatescommission");
        $titleStatus = \Lang::get("affiliatesstatus");
        $referralsTable .= "<table cellspacing=\"1\" bgcolor=\"#cccccc\" width=\"100%\">\n    <tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\">\n        <td>" . $titleSignupDate . "</td>\n        <td>" . $titleProduct . "</td>\n        <td>" . $titleAmount . "</td>\n        <td>" . $titleBillingCycle . "</td>\n        <td>" . $titleCommission . "</td>\n        <td>" . $titleStatus . "</td>\n    </tr>";
        $service = "";
        $firstOfLastMonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
        $lastOfLastMonth = mktime(23, 59, 59, date("m"), date("d") - 1, date("Y"));
        $affiliatesAccounts = DB::table("tblaffiliatesaccounts")->where("affiliateid", "=", $affiliateID)->whereBetween("tblhosting.regdate", array(date("Y-m-d", $firstOfLastMonth), date("Y-m-d", $lastOfLastMonth)))->join("tblhosting", "tblhosting.id", "=", "tblaffiliatesaccounts.relid")->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")->join("tblclients", "tblclients.id", "=", "tblhosting.userid")->orderBy("regdate", "DESC")->get(array("tblaffiliatesaccounts.*", "tblproducts.name", "tblhosting.packageid", "tblhosting.userid", "tblhosting.domainstatus", "tblhosting.amount", "tblhosting.firstpaymentamount", "tblhosting.regdate", "tblhosting.billingcycle"));
        foreach ($affiliatesAccounts as $affiliateAccount) {
            $hostingRelatedID = $affiliateAccount->relid;
            $referredUserID = $affiliateAccount->userid;
            $amount = $affiliateAccount->amount;
            $date = $affiliateAccount->regdate;
            $service = \App\Models\Product::getProductName($affiliateAccount->packageid, $affiliateAccount->name);
            $billingCycle = $affiliateAccount->billingcycle;
            $hostingStatus = $affiliateAccount->domainstatus;
            if ($billingCycle == "One Time") {
                $amount = $affiliateAccount->firstpaymentamount;
            }
            $commission = \App\Helpers\Functions::calculateAffiliateCommission($affiliateID, $hostingRelatedID);
            $currency = \App\Helpers\Format::getCurrency($referredUserID);
            $amount = \App\Helpers\Format::formatCurrency($amount);
            $commission = \App\Helpers\Format::formatCurrency($commission);
            $date = (new \App\Helpers\Client())->fromMySQLDate($date, 0, 1);
            $hostingStatus = \Lang::get("clientarea" . strtolower($hostingStatus));
            $billingCycle = strtolower($billingCycle);
            $billingCycle = str_replace(array(" ", "-"), "", $billingCycle);
            $billingCycle = \Lang::get("orderpaymentterm" . $billingCycle);
            $referralsTable .= "    <tr bgcolor=\"#ffffff\" style=\"text-align:center;\">\n        <td>" . $date . "</td>\n        <td>" . $service . "</td>\n        <td>" . $amount . "</td>\n        <td>" . $billingCycle . "</td>\n        <td>" . $commission . "</td>\n        <td>" . $hostingStatus . "</td>\n    </tr>";
        }
        if (!$service) {
            $titleNoSignups = \Lang::get("affiliatesnosignups");
            $referralsTable .= "    <tr bgcolor=\"#ffffff\">\n        <td colspan=\"6\" align=\"center\">" . $titleNoSignups . "</td>\n    </tr>";
        }
        $referralsTable .= "</table>";
        $systemURL = config('app.url')."/";
        $email_merge_fields = array();
        $email_merge_fields["affiliate_total_visits"] = $visitors;
        $email_merge_fields["affiliate_balance"] = $balance;
        $email_merge_fields["affiliate_withdrawn"] = $withdrawn;
        $email_merge_fields["affiliate_referrals_table"] = $referralsTable;
        $email_merge_fields["affiliate_referral_url"] = (string) $systemURL . "aff.php?aff=" . $id;
        $this->massAssign($email_merge_fields);
    }
}

?>
