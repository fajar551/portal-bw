<?php

namespace App\Http\Controllers\Client;

use App\Helpers\Client;
use App\Helpers\LogActivity;
use App\Helpers\Password;
use App\Helpers\Sanitize;
use Illuminate\Support\Facades\Lang;
use App\Helpers\Validate;
use App\Http\Controllers\Controller;
use App\Models\Client as ModelsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;
use PragmaRX\Google2FA\Google2FA as BaseGoogle2FA;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    //    public function EditAccountDetails()
    //    {
    //       $auth = Auth::user();
    //       $initCountries = new Client();
    //       $countries = $initCountries->getCountries();

    //       return view('pages.profile.editaccountdetails.index', ['auth' => $auth, 'countries' => $countries]);
    //    }
    public function EditAccountDetails()
    {
        try {
            $auth = Auth::user();
            if (!$auth) {
                return redirect('/login');
            }

            $initCountries = new Client();
            $countries = $initCountries->getCountries();

            return view('pages.profile.editaccountdetails.index', [
                'auth' => $auth,
                'countries' => $countries
            ]);
        } catch (Exception $e) {
            return redirect('/login');
        }
    }

    public function UpdateAccountDetails(Request $request)
    {

        $rules = [
            'firstName'  => 'required',
            'lastName'   => 'required',
            'companyName'   => 'required',
            'email'   => 'required',
            'phone'   => 'required',
            'tax_id'   => 'nullable',
            'address1'   => 'required',
            'address2'   => 'nullable',
            'city'   => 'required',
            'state'   => 'required',
            'postalCode'   => 'required',
            'country'   => 'required',
        ];
        $messages = [
            'firstName.required'    => 'Name required.',
            'lastName.required'    => 'Last Name required.',
            'companyName.required'    => 'Company Name required.',
            'email.required'    => 'Email required.',
            'phone.required'    => 'Phone required.',
            'tax_id.nullable'    => 'Tax ID is optional.',
            'address1.required'    => 'Address required.',
            'city.required'    => 'City required.',
            'state.required'    => 'State required.',
            'postalCode.required'    => 'Postal Code required.',
            'country.required'    => 'Country required.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['error' => $messages]);
        }

        $id = $request->userid;
        $updateClient = ModelsClient::findOrFail($id);
        $updateClient->firstname = $request->firstName;
        $updateClient->lastname = $request->lastName;
        $updateClient->companyname = $request->companyName;
        $updateClient->email = $request->email;
        $updateClient->phonenumber = $request->phone;
        $updateClient->tax_id = $request->tax_id ?? "";
        $updateClient->address1 = $request->address1;
        $updateClient->address2 = $request->address2 ?? "";
        $updateClient->city = $request->city;
        $updateClient->state = $request->state;
        $updateClient->postcode = $request->postalCode;
        $updateClient->country = $request->country;
        $updateClient->save();
        return redirect()->back()->withErrors($validator)->withInput()->with(['success' => 'Profile Updated!']);
    }

    public function UpdatePassword(Request $request)
    {
        $auth = Auth::user();
        $validate = new Validate();
        $hasher = new Password();

        $prevPassword = Sanitize::decode($request->prevPassword);
        $newPassword = Sanitize::decode($request->newPassword);
        $confirmPassword = Sanitize::decode($request->confirmPassword);
        $userid = $auth->id;

        $getPasswords = ModelsClient::select('password')->where('id', $userid)->get();
        foreach ($getPasswords as $pw) {
            $storedPassHash = $pw->password;
        }

        $checkPrevPassword = $hasher->verify($prevPassword, $storedPassHash);
        $errMsg = [];
        if (!$checkPrevPassword) {
            $errMsg[] = __('client.existingpasswordincorrect');
        }
        if ($newPassword !== $confirmPassword) {
            $errMsg[] = __('client.clientareaerrorpasswordnotmatch');
        }
        if (!$confirmPassword) {
            $errMsg[] = __('client.clientareaerrorpasswordconfirm');
        }

        if ($errMsg) {
            return redirect()->back()->with('error_pass', $errMsg);
        } else {
            $updatedPassword = $hasher->hash($newPassword);
            $updateClient = ModelsClient::findOrFail($userid);
            $updateClient->password = $updatedPassword;
            $updateClient->save();
            \App\Helpers\Hooks::run_hook("ClientChangePassword", array("userid" => $userid, "password" => $newPassword));
            LogActivity::Save("Modified Password - User ID: " . $userid);
            return redirect()->route('pages.profile.changepassword.index')->with('success_pass', "Password has been changed!");
        }
    }

    public function EmailNotes()
    {
        $auth = Auth::user();
        return view('pages.profile.emailnotes.index');
    }


    // public function SecuritySettings(Request $request)
    // {
    //     try {
    //         $auth = Auth::guard('web')->user();
    //         if (!$auth) {
    //             throw new \Exception('User not authenticated');
    //         }

    //         $twofa = new \App\Helpers\TwoFactorAuthentication();
    //         $twofa->setClientID($auth->id);

    //         $client = \App\Models\Client::find($auth->id);
    //         $twoFactorAuthEnabled = !empty($client->authmodule) && !empty($client->authdata);

    //         Log::debug('2FA Status Check', [
    //             'user_id' => $auth->id,
    //             'authmodule' => $client->authmodule,
    //             'authdata_exists' => !empty($client->authdata)
    //         ]);

    //         $smartyvalues['twoFactorAuthAvailable'] = true;
    //         $smartyvalues['twoFactorAuthEnabled'] = $twoFactorAuthEnabled;
    //         $smartyvalues['twofaavailable'] = true;
    //         $smartyvalues['twofastatus'] = $twoFactorAuthEnabled;

    //         return view('pages.profile.securitysettings.index', [
    //             'twoFactorAuthEnabled' => $smartyvalues['twoFactorAuthEnabled'],
    //             'twoFactorAuthAvailable' => $smartyvalues['twoFactorAuthAvailable'],
    //             'smartyvalues' => $smartyvalues
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Security Settings Error:', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return redirect()
    //             ->back()
    //             ->with('error', 'An error occurred while loading security settings.');
    //     }
    // }
    public function SecuritySettings(Request $request)
    {
        try {
            $auth = Auth::guard('web')->user();
            if (!$auth) {
                throw new \Exception('User not authenticated');
            }

            $twofa = new \App\Helpers\TwoFactorAuthentication();
            $twofa->setClientID($auth->id);

            $client = \App\Models\Client::find($auth->id);

            // Check if 2FA is enabled based on authmodule value
            $twoFactorAuthEnabled = $client->authmodule === 'yes';

            Log::debug('2FA Status Check', [
                'user_id' => $auth->id,
                'authmodule' => $client->authmodule,
                'authdata_exists' => !empty($client->authdata),
                'is_enabled' => $twoFactorAuthEnabled
            ]);

            // Prepare data for view
            $viewData = [
                'twoFactorAuthEnabled' => $twoFactorAuthEnabled,
                'twoFactorAuthAvailable' => true, // Assuming 2FA is always available
                'user' => [
                    'firstname' => $auth->firstname,
                    'lastname' => $auth->lastname,
                    'email' => $auth->email
                ],
                // Include backup code if it exists in session
                'backupCode' => session('backupCode'),
                // Add any flash messages
                'success' => session('success'),
                'error' => session('error'),
                'show_setup_modal' => session('show_setup_modal')
            ];

            return view('pages.profile.securitysettings.index', $viewData);
        } catch (\Exception $e) {
            Log::error('Security Settings Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while loading security settings: ' . $e->getMessage());
        }
    }

    /**
     * Handle security question form submission
     */
    private function handleSecurityQuestionSubmission($request, $clientsDetails, $legacyClient)
    {
        $errormessage = '';

        $currentsecurityqans = $request->input('currentsecurityqans');
        $securityqans = $request->input('securityqans');
        $securityqans2 = $request->input('securityqans2');
        $securityqid = $request->input('securityqid');

        // Validate current answer
        if ($clientsDetails['securityqid'] && $clientsDetails['securityqans'] != $currentsecurityqans) {
            $errormessage .= "<li>" . Lang::get("client.securitycurrentincorrect");
        }

        // Validate new answer
        if (empty($securityqans)) {
            $errormessage .= "<li>" . Lang::get("client.securityanswerrequired");
        }

        // Validate answer confirmation
        if ($securityqans !== $securityqans2) {
            $errormessage .= "<li>" . Lang::get("client.securitybothnotmatch");
        }

        // Update if no errors
        if (empty($errormessage)) {
            \App\Models\Client::where([
                'id' => $legacyClient->getID()
            ])->update([
                'securityqid' => $securityqid,
                'securityqans' => (new \App\Helpers\Pwd)->encrypt($securityqans)
            ]);

            \App\Helpers\LogActivity::Save(
                "Modified Security Question - User ID: " . $legacyClient->getID()
            );
        }

        return $errormessage;
    }

    public function disableTwoFactor(Request $request)
    {
        try {
            $auth = Auth::guard('web')->user();
            if (!$auth) {
                throw new \Exception('User not authenticated');
            }

            if (!Hash::check($request->password, $auth->password)) {
                throw new \Exception('Incorrect password');
            }

            // Update user record
            $client = ModelsClient::find($auth->id);
            $client->authmodule = 'no'; // Set authmodule ke 'no'
            $client->authdata = null; // Hapus data autentikasi
            $client->save();

            $twofa = new \App\Helpers\TwoFactorAuthentication();
            $twofa->setClientID($auth->id);

            $result = $twofa->disable();

            if (!$result) {
                throw new \Exception('Failed to disable 2FA');
            }

            \App\Helpers\LogActivity::Save("Two-Factor Authentication Disabled - User ID: " . $auth->id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function setupTwoFactor(Request $request)
    {
        try {
            Log::info('Setup 2FA started');

            $auth = Auth::guard('web')->user();
            if (!$auth) {
                throw new \Exception('User not authenticated');
            }

            Log::info('User authenticated:', ['user_id' => $auth->id, 'email' => $auth->email]);

            // Generate secret key
            $secretKey = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ234567"), 0, 16));
            Log::info('Secret key generated:', ['secret' => $secretKey]);

            // Generate QR Code URL dengan format yang benar
            $companyName = config('app.name', 'Hosting_NVME');
            $userEmail = $auth->email;

            $qrCodeUrl = sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode($companyName),
                rawurlencode($userEmail),
                $secretKey,
                rawurlencode($companyName)
            );

            Log::info('QR Code URL generated:', [
                'company' => $companyName,
                'email' => $userEmail,
                'qrCodeUrl' => $qrCodeUrl
            ]);

            // Store secret key in session
            session(['2fa_secret' => $secretKey]);

            if ($request->ajax()) {
                Log::info('Sending AJAX response');
                return response()->json([
                    'qrCodeUrl' => $qrCodeUrl,
                    'secret' => $secretKey
                ]);
            }

            Log::info('Rendering view');
            return view('pages.profile.securitysettings.index', [
                'qrCodeUrl' => $qrCodeUrl,
                'secret' => $secretKey
            ]);
        } catch (\Exception $e) {
            Log::error('Setup 2FA failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'details' => 'Check logs for more information'
                ], 500);
            }

            return redirect()
                ->route('pages.profile.securitysettings.index')
                ->with('error', 'Failed to setup Two Factor Authentication: ' . $e->getMessage());
        }
    }

    public function enableTwoFactor(Request $request)
    {
        try {
            $auth = Auth::guard('web')->user();
            if (!$auth) {
                throw new \Exception('User not authenticated');
            }

            $request->validate([
                'code' => 'required|numeric|digits:6'
            ]);

            $secret = session('2fa_secret');
            if (!$secret) {
                throw new \Exception('Setup process not initiated');
            }

            $google2fa = new BaseGoogle2FA();
            $valid = $google2fa->verifyKey($secret, $request->code, 2);

            if (!$valid) {
                throw new \Exception('Invalid verification code');
            }

            // Update user record
            $client = ModelsClient::find($auth->id);
            $client->authmodule = 'yes'; // Set authmodule ke 'yes'
            $client->save();

            $twofa = new \App\Helpers\TwoFactorAuthentication();
            $twofa->setClientID($auth->id);

            $result = $twofa->enable([
                'module' => 'totp',
                'secret' => $secret
            ]);

            if (!$result['success']) {
                throw new \Exception('Failed to enable 2FA');
            }

            // Store the backup code in the user's record
            $client = ModelsClient::find($auth->id);
            $client->backup_code = $result['backupCode'];
            $client->save();

            session()->forget('2fa_secret');
            LogActivity::Save("Two-Factor Authentication Enabled - User ID: " . $auth->id);

            return redirect()
                ->route('pages.profile.securitysettings.index')
                ->with('success', __('client.twofaenabled'))
                ->with('backupCode', $result['backupCode']);
        } catch (\Exception $e) {
            return redirect()
                ->route('pages.profile.securitysettings.index')
                ->with('error', 'Failed to enable Two Factor Authentication: ' . $e->getMessage());
        }
    }

    // Helper function untuk generate TOTP code
    private function getCode($secret, $timeSlice)
    {
        $secretkey = $this->base32Decode($secret);

        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);

        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);

        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;

        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpack binary value
        $value = unpack('N', $hashpart)[1];

        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, 6);

        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    // Helper function untuk decode Base32
    private function base32Decode($secret)
    {
        $base32chars = array(
            'A' => 0,
            'B' => 1,
            'C' => 2,
            'D' => 3,
            'E' => 4,
            'F' => 5,
            'G' => 6,
            'H' => 7,
            'I' => 8,
            'J' => 9,
            'K' => 10,
            'L' => 11,
            'M' => 12,
            'N' => 13,
            'O' => 14,
            'P' => 15,
            'Q' => 16,
            'R' => 17,
            'S' => 18,
            'T' => 19,
            'U' => 20,
            'V' => 21,
            'W' => 22,
            'X' => 23,
            'Y' => 24,
            'Z' => 25,
            '2' => 26,
            '3' => 27,
            '4' => 28,
            '5' => 29,
            '6' => 30,
            '7' => 31
        );

        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = array(6, 4, 3, 1, 0);

        if (!in_array($paddingCharCount, $allowedValues)) {
            return false;
        }

        for ($i = 0; $i < 4; $i++) {
            if (
                $paddingCharCount == $allowedValues[$i] &&
                substr($secret, - ($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])
            ) {
                return false;
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);

        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], array_keys($base32chars))) {
                return false;
            }
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32chars[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }
}
