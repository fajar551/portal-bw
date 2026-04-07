<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\Client as HelpersClient;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Contact;
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientContactController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.index")
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> ') .__('admin.clientsinvalidclientid'));
        }

        $userid = $request->userid;
        $contactid = $request->contactid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["countries"] = (new HelpersClient())->getCountries();
        $templatevars["allPermissions"] = Contact::$allPermissions;
        $templatevars["addnew"] = true;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        if ($contactid) {
            $templatevars["addnew"] = false;

            $contact = Contact::where("userid", $userid)->where("id",$contactid)->first();
            if (!$contact) {
                return redirect()
                        ->route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $userid])
                        ->withInput()
                        ->with('type', 'danger')
                        ->with('message', __('<b>Oh No!</b> Invalid Contact ID.'));
            }

            $contact->permissions = explode(",", $contact->permissions);
            $templatevars["contact"] = $contact;
        }

        /*
         * TODO: remoteAuth
        $remoteAuth = new WHMCS\Authentication\Remote\RemoteAuth();
        foreach ($contact->remoteAccountLinks()->get() as $remoteAccountLink) {
            $provider = $remoteAuth->getProviderByName($remoteAccountLink->provider);
            $remoteAccountLinks[$remoteAccountLink->id] = $provider->parseMetadata($remoteAccountLink->metadata);
        }
         */
        return view('pages.clients.viewclients.clientcontacts.index', $templatevars);
    }

    public function create(Request $request)
    {
        $userid = $request->userid;

        if (!auth()->user()->checkPermissionTo("Edit Clients Details")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'firstname' => "required|string",
            'lastname' => "nullable|string",
            'companyname' => "nullable|string",
            "email" => "required|string|email|unique:App\Models\Contact,email",
            "password" => "nullable",
            "tax_id" => "nullable|string",
            "address1" => "required|string",
            "address2" => "nullable|string",
            "city" => "required|string",
            "state" => "required|string",
            "postcode" => "nullable|numeric",
            "country" => "required|string|max:2",
            "phonenumber" => "required|string|max:14|min:6",
            "email_preferences" => "nullable|array",
            "email_preferences.*" => $request->email_preferences ? "required|numeric|in:0,1" : "",
            "permissions" => "nullable|array",
            "permissions.*" => $request->permissions ? "required|string|in:".implode(",", Contact::$allPermissions) : "",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b>  Please ensure to fill all fields correctly and re-submit the form.'));
        }
        
        $response = (new HelpersClient())->AddContact2($request->all());

        if ($response["result"] == "error") {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', $response["message"]);
        }

        return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid])
                    ->with('type', 'success')
                    ->with('message', __("<b>Well Done!</b> Contact added succesfully. Contact ID: {$response["contactid"]}"));
    }

    public function update(Request $request)
    {
        $userid = $request->userid;
        $contactid = $request->contactid;

        if (!auth()->user()->checkPermissionTo("Edit Clients Details")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $userid, 'contactid' => $contactid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'contactid' => "required|integer|exists:App\Models\Contact,id",
            'firstname' => "required|string",
            'lastname' => "nullable|string",
            'companyname' => "nullable|string",
            "email" => "required|string|email|unique:App\Models\Contact,email,".$contactid,
            "password" => "nullable",
            "tax_id" => "nullable|string",
            "address1" => "required|string",
            "address2" => "nullable|string",
            "city" => "required|string",
            "state" => "required|string",
            "postcode" => "nullable|numeric",
            "country" => "required|string|max:2",
            "phonenumber" => "required|string|max:14|min:6",
            "email_preferences" => "nullable|array",
            "email_preferences.*" => $request->email_preferences ? "required|numeric|in:0,1" : "",
            "permissions" => "nullable|array",
            "permissions.*" => $request->permissions ? "required|string|in:".implode(",", Contact::$allPermissions) : "",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid, 'contactid' => $contactid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b>  Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $params = $request->all();
        $params["password2"] = $request->password;
        $response = (new HelpersClient())->UpdateContact($params);

        if ($response["result"] == "error") {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid, 'contactid' => $contactid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', $response["message"]);
        }

        return redirect()
                    ->route("admin.pages.clients.viewclients.clientcontacts.index", ["userid" => $userid])
                    ->with('type', 'success')
                    ->with('message', __("<b>Well Done!</b> Contact updated succesfully. Contact ID: {$response["contactid"]}"));
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $response = (new HelpersClient())->DeleteContact($id);

        if ($response["result"] == "error") {
            return ResponseAPI::Error([
                'message' => $response["message"],
            ]);
        }
        
        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function dtClientContact(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];

        $params = [
            "userid" => $userid,
            "pfx" => $pfx,
        ];

        $query = Contact::where("userid", $userid);
        
        return datatables()->of($query)
            ->addColumn('actions', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientcontacts.index', ['userid' => $params["userid"], 'contactid' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-userid=\"{$params["userid"]}\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\"><i class=\"fa fa-trash\"></i></button>";

                return $action;
            })
            ->rawColumns(['actions'])
            ->addIndexColumn()
            ->toJson();
    }
    
}
