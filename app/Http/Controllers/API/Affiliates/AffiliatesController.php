<?php

namespace App\Http\Controllers\API\Affiliates;

use Validator;
use ResponseAPI, Cfg, Affiliate as AffiliateHelper;

use App\Models\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AffiliatesController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * GetAffiliates
     * 
     * Obtain an array of affiliates
     */
    public function GetAffiliates()
    {
        $paytypes = ['percentage', 'fixedamount'];
        $paytypesString = implode(', ', $paytypes);

        $rules = [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'userid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            'visitors' => ['nullable', 'integer'],
            'paytype' => [
                'nullable',
                'string',
                Rule::in($paytypes),
            ],
            'payamount' => ['nullable'],
            'onetime' => ['nullable', 'integer'],
            'balance' => ['nullable'],
            'withdrawn' => ['nullable'],
        ];

        $messages = [
            'paytype.in' => "Invalid paytype. Should be {$paytypesString}",
            'userid.exists' => "Client ID not found",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $limitstart = $this->request->input('limitstart') ?? 0;
        $limitnum = $this->request->input('limitnum') ?? 25;
        $userid = $this->request->input('userid');
        $visitors = $this->request->input('visitors');
        $paytype = $this->request->input('paytype');
        $payamount = $this->request->input('payamount');
        $onetime = $this->request->input('onetime');
        $balance = $this->request->input('balance');
        $withdrawn = $this->request->input('withdrawn');

        $page = $limitstart + 1;
        $mulai = ($page > 1) ? ($page * $limitnum) - $limitnum : 0;

        $filters = [
            'clientid' => $userid,
            'visitors' => $visitors,
            'paytype' => $paytype,
            'payamount' => $payamount,
            'onetime' => $onetime,
            'balance' => $balance,
            'withdrawn' => $withdrawn,
        ];

        $query = Affiliate::query();
        $query->filter($filters);
        $totalresults = $query->count();
        $query->offset($mulai);
        $query->limit($limitnum);
        $results = $query->get();

        $response = [
            'affiliate' => $results,
        ];

        return ResponseAPI::Success([
            'totalresults' => $totalresults,
            'startnumber' => $limitstart,
            'numreturned' => $results->count(),
            'affiliates' => $results->count() > 0 ? $response : [],
        ]);
    }

    /**
     * AffiliateActivate
     * 
     * Activate affiliate referrals for a client.
     */
    public function AffiliateActivate()
    {
        $rules = [
            'userid' => ['required', 'integer', 'exists:App\Models\Client,id'],
        ];

        $messages = [
            'userid.exists' => "Client ID not found",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $userid = $this->request->input('userid');

        AffiliateHelper::Activate($userid);

        return ResponseAPI::Success();
    }
}
