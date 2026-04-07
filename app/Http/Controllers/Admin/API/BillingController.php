<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, DB;
use App\Helpers\ResponseAPI;
use Auth;

class BillingController extends Controller
{
    //
    public function massActionInvoiceItems(Request $request)
    {
        $action = $request->input('action');
        $itemids = $request->input('itemids') ?? [];
        switch ($action) {
            case 'delete':
                return $this->massDeleteInvoiceItems($request);
                break;
            case 'split':
                return $this->splitInvoiceItems($request);
                break;
            
            default:
                return ResponseAPI::Success();
                break;
        }
    }

    public function splitInvoiceItems(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('invoiceid');
            $itemids = $request->input('itemids') ?? [];

            $result = \App\Models\Invoice::findOrFail($id);
            $data = $result->toArray();
            $userid = $data['userid'];
            $date = $data['date'];
            $duedate = $data['duedate'];
            $taxrate = $data['taxrate'];
            $taxrate2 = $data['taxrate2'];
            $paymentmethod = $data['paymentmethod'];

            $result = \App\Models\Invoiceitem::where(array("invoiceid" => $id))->count();
            $data = $result;
            $totalitemscount = $data;
            if (count($itemids) < $totalitemscount) {
                $gateway = \App\Helpers\Gateway::getClientsPaymentMethod($userid);
                $invoice = \App\Models\Invoice::newInvoice($userid, $gateway, $taxrate, $taxrate2);
                $invoice->status = "Unpaid";
                $invoice->save();
                $invoiceid = $invoice->id;
                foreach ($itemids as $itemid) {
                    \App\Models\Invoiceitem::where(array("id" => $itemid))->update(array("invoiceid" => $invoiceid));
                }
                \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
                \App\Helpers\Invoice::updateInvoiceTotal($id);
                \App\Helpers\LogActivity::Save("Split Invoice - Invoice ID: " . $id . " to Invoice ID: " . $invoiceid, $userid);
                $invoiceArr = array("source" => "adminarea", "user" => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : "system", "invoiceid" => $invoiceid, "status" => "Unpaid");
                \App\Helpers\Hooks::run_hook("InvoiceCreation", $invoiceArr);
                \App\Helpers\Hooks::run_hook("InvoiceCreationAdminArea", $invoiceArr);
                \App\Helpers\Hooks::run_hook("InvoiceSplit", array("originalinvoiceid" => $id, "newinvoiceid" => $invoiceid));
                // success here
            }

            DB::commit();
            return ResponseAPI::Success([
                'message' => 'Items Splited',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function massDeleteInvoiceItems(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('invoiceid');
            $itemids = $request->input('itemids') ?? [];

            \App\Models\Invoiceitem::whereIn('id', $itemids)->delete();
            \App\Helpers\Invoice::updateInvoiceTotal($id);

            DB::commit();
            return ResponseAPI::Success([
                'message' => 'Items Deleted',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
