<?php

namespace App\Helpers;

// Import Model Class here
use App\Models\Product as ProductModel;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Product
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getProductName($id)
    {
        $product = ProductModel::find($id);
        if ($product) {
            return $product->name;
        }
        return '';
    }

    public static function getTaxStatus($id){
        $product = ProductModel::find($id);
        if ($product->tax == 1) {
            return $product->tax;
        }
        return 0;
    }

    public static function productDropDown($pid = 0, $noneopt = "", $anyopt = "")
    {
        $code = "";
        if ($anyopt) {
            $code .= "<option value=\"\">" . __("admin.any") . "</option>";
        }

        if ($noneopt) {
            $code .= "<option value=\"\">" . __("admin.none") . "</option>";
        }

        $groupname = "";
        $productsList = self::getProducts();
        // dd($productsList);
        foreach ($productsList as $data) {
            $packid = $data["id"];
            $gid = $data["gid"];
            $name = $data["name"];
            $packtype = $data["groupname"];
            if ($packtype != $groupname) {
                if (!$groupname) {
                    $code .= "</optgroup>";
                }
                $code .= "<optgroup label=\"" . $packtype . "\">";
                $groupname = $packtype;
            }
            if (!$data["retired"] || $pid == $packid) {
                $code .= "<option value=\"" . $packid . "\"";
                if ($pid == $packid) {
                    $code .= " selected";
                }
                $code .= ">" . $name . "</option>";
            }
        }

        $code .= "</optgroup>";

        return $code;
    }

    public static function getProducts($groupId = NULL)
    {
        $pfx = \Database::prefix();
        $query = ProductModel::select("{$pfx}products.id", "{$pfx}products.type", "{$pfx}products.gid", "{$pfx}products.retired", "{$pfx}products.name", "{$pfx}productgroups.name AS groupname", "{$pfx}productgroups.order as gorder", "{$pfx}products.order as porder")
            ->join("{$pfx}productgroups", "{$pfx}products.gid", "{$pfx}productgroups.id")
            ->orderByRaw("{$pfx}productgroups.order ASC, {$pfx}products.order ASC, name ASC");
        if ($groupId) {
            $query->where("tblproducts.gid", (int) $groupId);
        }

        return $query->get()->toArray();
    }

    public static function productStatusDropDown($status = "", $anyop = false, $name = "status", $id = "", $initWithSelectTag = false)
    {
        $statuses = ["Pending", "Active", "Completed", "Suspended", "Terminated", "Cancelled", "Fraud"];
        $code = "";

        if ($initWithSelectTag) {
            $code .= "<select name=\"" . $name . "\" class=\"form-control select-inline\"" . ($id ? " id=\"" . $id . "\"" : "") . ">";
        }

        if ($anyop) {
            $code .= "<option value=\"\">" . __("admin.any") . "</option>";
        }

        foreach ($statuses as $stat) {
            $code .= "<option value=\"" . $stat . "\"";
            if ($status == $stat) {
                $code .= " selected";
            }

            $code .= ">" . __("admin.status" . strtolower($stat)) . "</option>";
        }

        if ($initWithSelectTag) {
            $code .= "</select>";
        }

        return $code;
    }

    public static function productTypeDropDown($type = "")
    {
        $code = "";
        $code .= "<option value=\"hostingaccount\"" . ($type == "hostingaccount" || $type == "sharedhosting" ? "selected" : "")  . ">Shared Hosting</option>";
        $code .= "<option value=\"reselleraccount\"" . ($type == "reselleraccount" ? "selected" : "")  . ">Reseller Hosting</option>";
        $code .= "<option value=\"server\"" . ($type == "server" || $type == "vpsservers" ? "selected" : "")  . ">VPS/Server</option>";
        $code .= "<option value=\"other\"" . ($type == "other" || $type == "otherservices" ? "selected" : "")  . ">Product/Service</option>";

        return $code;
    }
}
