<?php

namespace App\Http\Controllers\API\Users;

use Validator;
use Auth;
use ResponseAPI;

use App\Models\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class UsersController extends Controller
{
    use SendsPasswordResetEmails;

    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * ResetPassword
     * 
     * Starts the password reset process for a user.
     */
    public function ResetPassword()
    {
        $clientTable = (new Client)->getTableName();

        $rules = [
            'id' => [
                'nullable',
                'integer',
                // 'exists:App\Models\Client,id',
                Rule::exists($clientTable, 'id')->where(function($query) {
                    $query->where("status", "!=", "Closed");
                }),
            ],
            'email' => [
                'nullable',
                'required_without:id',
                'email',
                // 'exists:App\Models\Client,email',
                Rule::exists($clientTable, 'email')->where(function($query) {
                    $query->where("status", "!=", "Closed");
                }),
            ],
        ];

        $messages = [
            'id.exists' => "Client ID Not Found",
            'email.exists' => "Client ID Not Found",
            'email.required_without' => "Please enter the email address or provide the id",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $id = $this->request->input('id');
        $email = $this->request->input('email');

        if ($id) {
            $client = Client::where("status", "!=", "Closed")->where("id", $id)->first();
            $email = $client->email;
        }

        $status = Password::sendResetLink(['email' => $email]);

        return ResponseAPI::success([
            'email' => $email,
            'status' => __($status),
        ]);
    }
}
