<?php

namespace App\Http\Controllers\API\Authentication;

use Validator;
use Auth;
use ResponseAPI, Format, Gateway, LogActivity;
use App\Rules\FloatValidator;

use App\Models\OauthserverClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * @group Authentication
 * 
 * APIs for managing authentication
 */
class AuthenticationController extends Controller
{
    //
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * ListOAuthCredentials
     * 
     * List OAuth Credentials matching passed criteria
     */
    public function ListOAuthCredentials()
    {
        $rules = [
            // Find credentials for a specific grant type
            'grantType' => ['nullable', 'string'],
            // Sort the response using the passed field
            'sortField' => ['nullable', 'string'],
            // The direction of the sort order (‘ASC’, ‘DESC’)
            'sortOrder' => ['nullable', 'string', Rule::in('ASC', 'DESC')],
            // To limit the number of returned credentials
            'limit' => ['nullable', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $grantType = $this->request->input('grantType');
        $sortField = $this->request->input('sortField');
        $sortOrder = $this->request->input('sortOrder');
        $limit = $this->request->input('limit');

        $filters = [
            'grant_type' => $grantType,
        ];

        $query = OauthserverClient::query();
        $query->where("id", "!=", 0);
        $query->filter($filters);
        if ($sortField) {
            $query->orderBy($sortField, $sortOrder);
        }
        if ($limit) {
            $query->limit($limit);
        }
        $results = $query->get();

        $response = [];
        foreach ($results as $data) {
            $response[] = [
                "credentialId" => $data->id,
                "name" => $data->name,
                "description" => $data->description,
                // TODO: grantTypes implode
                // "grantTypes" => implode(" ", $data->grantTypes),
                "scope" => $data->scope,
                "clientIdentifier" => $data->identifier,
                // TODO: clientSecret need depcrypted
                // "clientSecret" => $data->decryptedSecret,
                "uuid" => $data->uuid,
                "serviceId" => $data->service_id,
                "logoUri" => $data->logo_uri,
                "redirectUri" => $data->redirect_uri,
                "rsaKeyPairId" => $data->rsa_key_pair_id,
                "createdAt" => $data->created_at->format("jS F Y g:i:sa"),
                "updatedAt" => $data->updated_at->format("jS F Y g:i:sa"),
            ];
        }

        return ResponseAPI::Success([
            'clients' => $response,
        ]);
    }

    /**
     * DeleteOAuthCredential
     * 
     * Deletes an OAuth Credential Record.
     * Removes OAuth Credential record. This action cannot be undone.
     */
    public function DeleteOAuthCredential()
    {
        $rules = [
            // The credential id to be deleted
            'credentialId' => ['required', 'integer', 'exists:App\Models\OauthserverClient,id'],
        ];

        $messages = [
            'credentialId.exists' => "Invalid Credential ID provided.",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $credentialId = $this->request->input('credentialId');

        $client = OauthserverClient::find($credentialId);
        $client->delete();

        return ResponseAPI::Success([
            'credentialId' => $credentialId,
        ]);
    }

    /**
     * ValidateLogin
     * 
     * Validate user login credentials.
     * 
     * This command can be used to validate an email address and password against a registered user in WHMCS. On success, the userid and password hash will be returned which can be used to create an authenticated session by setting the session key ‘uid’ to the userid and the session key ‘upw’ to the passwordhash. Note: if session IP validation is enabled, this API call must be executed via the local API to receive a valid hash.
     */
    public function ValidateLogin()
    {
        $rules = [
            // User Email Address
            'email' => ['required', 'string'],
            // Password to validate
            'password2' => ['required', 'string'],
        ];

        $messages = [
            'email.exists' => "Invalid Email provided.",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $email = $this->request->input('email');
        $password2 = $this->request->input('password2');

        $client = \App\Models\Client::where('email', $email)->first();

        if (!$client) {
            return ResponseAPI::Error([
                'message' => "Invalid email",
            ]);
        }

        $clientPassword = $client->password;

        if (!Hash::check($password2, $clientPassword)) {
            return ResponseAPI::Error([
                'message' => "Invalid password",
            ]);
        }

        return ResponseAPI::Success([
            'userid' => $client->id,
        ]); 
    }

    /**
     * Login user
     * 
     * Authenticate user and return token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first()
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ResponseAPI::Error([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseAPI::Success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Register new user
     * 
     * Create new user account and return token
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first()
            ]);
        }

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseAPI::Success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Get authenticated user
     * 
     * Return current authenticated user data
     */
    public function whoami(Request $request)
    {
        return ResponseAPI::Success([
            'user' => $request->user()
        ]);
    }

    /**
     * Handle forgot password request
     * 
     * Send password reset link to user's email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first()
            ]);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return ResponseAPI::Success([
                'message' => 'Reset password link has been sent to your email'
            ]);
        }

        return ResponseAPI::Error([
            'message' => 'Unable to send reset link'
        ]);
    }
}
