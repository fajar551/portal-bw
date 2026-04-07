<?php
namespace App\Helpers;

use Format, Cfg, LogActivity;

// Import Model Class here
use App\Models\Client;
use App\Models\Affiliate as AffiliateModel;

// Import Package Class here
use App\Events\AffiliateActivation;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Affiliate
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public static function Activate($userid)
	{
		$client = Client::find($userid);
		$clientcurrency = $client->currency;
		$bonusdeposit = Format::ConvertCurrency(Cfg::get('AffiliateBonusDeposit'), 1, $clientcurrency);
		$affiliate = AffiliateModel::select('id')->where('clientid', $userid)->first();
		if (!$affiliate) {
			$newaffiliate = new AffiliateModel;
			$newaffiliate->date = \Carbon\Carbon::now()->format('Y-m-d');
			$newaffiliate->clientid = $userid;
			$newaffiliate->balance = $bonusdeposit;
			$newaffiliate->save();
			$affiliateid = $newaffiliate->id;
		} else {
			$affiliateid = $affiliate->id;
		}
		LogActivity::Save("Activated Affiliate Account - Affiliate ID: " . $affiliateid . " - User ID: " . $userid, $userid);
		
		\App\Helpers\Hooks::run_hook("AffiliateActivation", array("affid" => $affiliateid, "userid" => $userid));
	}

}
