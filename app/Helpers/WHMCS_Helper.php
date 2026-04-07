<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Cfg;
use App\Helpers\Ticket as TicketHelper;
use App\Helpers\Customfield as CustomfieldHelper;
use App\Models\Contact;
use App\Models\Hosting;
use App\Models\Domain;
use App\Models\Client;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Paymentgateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WHMCS_Helper
{
    public function CreateInvoice(array $postData)
    {
        $rules = [
            'userid' => ['required', 'integer', 'exists:App\Models\Client,id'],
            'status' => ['nullable', 'string'],
            'draft' => ['nullable', 'boolean'],
            'sendinvoice' => ['nullable', 'boolean'],
            'paymentmethod' => ['nullable', 'string'],
            'taxrate' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            'taxrate2' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'duedate' => ['nullable', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string'],
            'itemdescription' => ['nullable', 'string'],
            'itemamount' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            'itemtaxed' => ['nullable', 'boolean'],
            'autoapplycredit' => ['nullable', 'boolean'],
        ];

        $messages = [
            'userid.exists' => "Client ID Not Found",
            'taxrate.regex' => ':Attribute must be in decimal format: ### or ###.##',
            'taxrate2.regex' => ':Attribute must be in decimal format: ### or ###.##',
            'itemamount.regex' => ':Attribute must be in decimal format: ### or ###.##',
        ];

        $validator = Validator::make($postData, $rules, $messages);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            Log::error('Invoice creation validation failed: ' . $errorMessage, ['postData' => $postData]);
            return ['result' => 'error', 'message' => $errorMessage];
        }

        DB::beginTransaction();
        try {
            $userid = $postData['userid'];
            $sendInvoice = $postData['sendinvoice'] ?? false;
            $paymentMethod = $postData['paymentmethod'] ?? null;
            $status = $postData['status'] ?? 'Unpaid';
            $createAsDraft = (bool) ($postData['draft'] ?? false);
            $invoiceStatuses = \App\Models\Invoice::getInvoiceStatusValues();
            $doprocesspaid = false;

            if ($createAsDraft && $sendInvoice) {
                $errorMessage = "Cannot create and send a draft invoice in a single API request. Please create and send separately.";
                Log::error('Invoice creation logic error: ' . $errorMessage, ['postData' => $postData]);
                return ['result' => 'error', 'message' => $errorMessage];
            }

            $taxrate = $postData['taxrate'] ?? null;
            $taxrate2 = $postData['taxrate2'] ?? null;

            if ($createAsDraft) {
                $status = "Draft";
            } else {
                if (!in_array($status, $invoiceStatuses)) {
                    $status = "Unpaid";
                }
            }

            $dateCreated = $postData['date'] ? \App\Helpers\Carbon::createFromFormat('Y-m-d', $postData['date']) : now();
            $dueDate = $postData['duedate'] ? \App\Helpers\Carbon::createFromFormat('Y-m-d', $postData['duedate']) : now()->addDays(7);

            $invoice = \App\Models\Invoice::newInvoice($userid, $paymentMethod, $taxrate, $taxrate2);
            $invoice->dateCreated = $dateCreated;
            $invoice->dateDue = $dueDate;
            $invoice->status = $status;
            $invoice->notes = $postData['notes'] ?? '';
            $invoice->save();

            $invoiceid = $invoice->id;
            $invoiceArr = ["source" => "api", "user" => Auth::id() ?? 0, "invoiceid" => $invoiceid, "status" => $status];

            if (isset($postData['itemdescription']) && isset($postData['itemamount'])) {
                \App\Models\Invoiceitem::insert([
                    "invoiceid" => $invoiceid,
                    "userid" => $userid,
                    "description" => $postData['itemdescription'],
                    "amount" => $postData['itemamount'],
                    "taxed" => $postData['itemtaxed'] ?? false
                ]);
            }

            \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
            \App\Helpers\Hooks::run_hook("InvoiceCreation", $invoiceArr);

            if ($postData['autoapplycredit'] ?? false) {
                $client = \App\Models\Client::find($userid);
                $credit = $client->credit;
                if ($credit > 0) {
                    $total = $invoice->total;
                    if ($total <= $credit) {
                        $credit = $total;
                        $doprocesspaid = true;
                    }
                    $client->credit -= $credit;
                    $client->save();
                    $invoice->credit = $credit;
                    $invoice->save();
                    \App\Models\Credit::create([
                        "clientid" => $userid,
                        "date" => now(),
                        "description" => "Credit Applied to Invoice #$invoiceid",
                        "amount" => -$credit
                    ]);
                    \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
                }
            }

            if ($sendInvoice) {
                \App\Helpers\Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
                $paymentType = \App\Models\Paymentgateway::where('gateway', $paymentMethod)->value('value') ?? '';
                $emailTemplate = in_array($paymentType, ["CC", "OfflineCC"]) ? "Credit Card Invoice Created" : "Invoice Created";
                $template = \App\Models\Emailtemplate::where("name", $emailTemplate)->first();
                \App\Helpers\Functions::sendMessage($template, $invoiceid);
            }

            if ($status != "Draft") {
                \App\Helpers\Hooks::run_hook("InvoiceCreated", $invoiceArr);
            }

            if ($doprocesspaid) {
                \App\Helpers\Invoice::processPaidInvoice($invoiceid);
            }

            DB::commit();
            return ['result' => 'success', 'invoiceid' => $invoiceid, "status" => $status];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Invoice creation failed: ' . $e->getMessage(), ['postData' => $postData]);
            return ['result' => 'error', 'message' => 'An error occurred while creating the invoice. Please check the logs for more details.'];
        }
    }

    public function openTicket(array $postData)
    {
        $contactTable = (new Contact)->getTableName();

        $rules = [
            'deptid' => ['required', 'integer', 'exists:App\Models\Ticketdepartment,id'],
            'subject' => ['required', 'string'],
            'message' => ['required', 'string'],
            'clientid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            'contactid' => [
                'nullable',
                'integer',
                Rule::exists($contactTable, 'id')->where(function($q) use ($postData) {
                    $clientid = $postData['clientid'] ?? null;
                    $q->where('userid', $clientid);
                }),
            ],
            'name' => ['required_without:clientid'],
            'email' => ['nullable', 'required_without:clientid', 'email'],
            'priority' => ['nullable', 'string', Rule::in(['Low', 'Medium', 'High'])],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'serviceid' => ['nullable', 'integer'],
            'domainid' => ['nullable', 'integer'],
            'admin' => ['nullable', 'boolean'],
            'markdown' => ['nullable', 'boolean'],
            'customfields' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3000'],
        ];

        $messages = [
            'deptid.exists' => 'Department ID Not Found',
            'clientid.exists' => 'Client ID Not Found',
            'contactid.exists' => 'Contact ID Not Found',
            'name.required_without' => 'Name and email address are required if not a client',
            'email.required_without' => 'Name and email address are required if not a client',
        ];

        $validator = Validator::make($postData, $rules, $messages);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return ['result' => 'error', 'message' => $error];
        }

        $deptid = $postData['deptid'];
        $subject = $postData['subject'];
        $message = $postData['message'];
        $clientid = $postData['clientid'] ?? null;
        $contactid = $postData['contactid'] ?? 0;
        $name = $postData['name'] ?? '';
        $email = $postData['email'] ?? '';
        $priority = $postData['priority'] ?? 'Medium';
        $created = $postData['created'] ?? null;
        $serviceid = $postData['serviceid'] ?? 0;
        $domainid = $postData['domainid'] ?? null;
        $admin = $postData['admin'] ?? false;
        $useMarkdown = $postData['markdown'] ?? false;
        $customfields = $postData['customfields'] ?? null;
        $attachments = $postData['attachments'] ?? [];

        if ($customfields) {
            $customfields = base64_decode($customfields);
            $customfields = (new \App\Helpers\Client())->safe_unserialize($customfields);
        }
        if (!is_array($customfields)) {
            $customfields = [];
        }

        $from = ["name" => "", "email" => ""];
        if (!$clientid) {
            $from = ["name" => $name, "email" => $email];
        }

        if ($serviceid) {
            if (is_numeric($serviceid) || substr($serviceid, 0, 1) == "S") {
                $hosting = Hosting::where('id', $serviceid)->where('userid', $clientid)->first();
                if (!$hosting) {
                    return ['result' => 'error', 'message' => 'Service ID Not Found'];
                }
                $serviceid = "S" . $hosting->id;
            } else {
                $serviceid = substr($serviceid, 1);
                $domain = Domain::where('id', $serviceid)->where('userid', $clientid)->first();
                if (!$domain) {
                    return ['result' => 'error', 'message' => 'Service ID Not Found'];
                }
                $serviceid = "D" . $domain->id;
            }
        }

        if ($domainid) {
            $domain = Domain::where('id', $domainid)->where('userid', $clientid)->first();
            if (!$domain) {
                return ['result' => 'error', 'message' => 'Domain ID Not Found'];
            }
            $serviceid = "D" . $domain->id;
        }

        $treatAsAdmin = $admin ? true : false;
        $validationData = [
            "clientId" => $clientid,
            "contactId" => $contactid,
            "name" => $name,
            "email" => $email,
            "isAdmin" => $treatAsAdmin,
            "departmentId" => $deptid,
            "subject" => $subject,
            "message" => $message,
            "priority" => $priority,
            "relatedService" => $serviceid,
            "customfields" => $customfields
        ];

        $ticketOpenValidateResults = \App\Helpers\Hooks::run_hook("TicketOpenValidation", $validationData);
        if (is_array($ticketOpenValidateResults)) {
            $hookErrors = [];
            foreach ($ticketOpenValidateResults as $hookReturn) {
                if (is_string($hookReturn) && ($hookReturn = trim($hookReturn))) {
                    $hookErrors[] = $hookReturn;
                }
            }
            if ($hookErrors) {
                return ['result' => 'error', 'message' => implode(". ", $hookErrors)];
            }
        }

        $attachmentString = [];
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $uuid = (string) Str::uuid();
                $fileNameToSave = Str::random(6) . "_" . $attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";

                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);

        $noemail = "";
        $ticketdata = TicketHelper::OpenNewTicket($clientid, $contactid, $deptid, $subject, $message, $priority, $attachmentString, $from, $serviceid, $cc = "", $noemail, $treatAsAdmin, $useMarkdown);

        if ($customfields) {
            CustomfieldHelper::SaveCustomFields($ticketdata["ID"], $customfields, "support", true);
        }

        return ['result' => 'success', 'data' => ["id" => $ticketdata["ID"], "tid" => $ticketdata["TID"], "c" => $ticketdata["C"]]];
    }

    public function getInvoice(array $postData)
    {
        $rules = [
            'invoiceid' => ['required', 'integer'],
        ];

        $validator = Validator::make($postData, $rules);

        if ($validator->fails()) {
            return ['result' => 'error', 'message' => $validator->errors()->first()];
        }

        $invoiceid = $postData['invoiceid'];
        $invoice = Invoice::find($invoiceid);

        if (!$invoice) {
            return ['result' => 'error', 'message' => 'Invoice ID Not Found'];
        }

        $data = $invoice->toArray();
        $account = Account::select(DB::raw('SUM(amountin)-SUM(amountout) as sumamount'))
            ->where('invoiceid', $invoiceid)
            ->first();
        $amountpaid = $account->sumamount;
        $balance = Format::AsCurrency($data["total"] - $amountpaid);

        $paymentgateway = Paymentgateway::where('gateway', $data["paymentmethod"])
            ->where('setting', 'type');
        $gatewaytype = $paymentgateway->value('value') ?? "";
        $ccgateway = in_array($gatewaytype, ["CC", "OfflineCC"]);

        $response = array_merge($data, [
            "balance" => $balance,
            "ccgateway" => $ccgateway,
        ]);

        $response["items"]["item"] = Invoiceitem::where('invoiceid', $invoiceid)
            ->get()
            ->map(function ($invoiceitem) {
                return [
                    "id" => $invoiceitem->id,
                    "type" => $invoiceitem->type,
                    "relid" => $invoiceitem->relid,
                    "description" => $invoiceitem->description,
                    "amount" => $invoiceitem->amount,
                    "taxed" => $invoiceitem->taxed,
                ];
            })
            ->toArray();

        $response["transactions"]["transaction"] = Account::where('invoiceid', $invoiceid)
            ->get()
            ->toArray();

        if (empty($response["transactions"]["transaction"])) {
            $response["transactions"] = "";
        }

        return ['result' => 'success', 'data' => $response];
    }

}
