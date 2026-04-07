<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Products
 * 
 * APIs for managing products
 */
class ProductsController extends Controller
{
    /**
     * AddProduct
     * 
     * Adds a product to the system to be available for purchase
     */
    public function AddProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // The name of the product to be added
            'name' => ['required', 'string'],
            // The id of the product group to add the product
            'gid' => ['required', 'integer'],
            // One of ‘hostingaccount’, ‘reselleraccount’, ‘server’ or ‘other’
            'type' => ['nullable', 'string'],
            // Set to true to enable stock control on the product
            'stockcontrol' => ['nullable', 'boolean'],
            // How much of this product is in stock
            'qty' => ['nullable', 'integer'],
            // The payment type of the product. One of ‘free’, ‘onetime’, ‘recurring’
            'paytype' => ['nullable', 'string'],
            // Should the product be hidden from the client order form
            'hidden' => ['nullable', 'boolean'],
            // Should the product show the domain registration options.
            'showdomainoptions' => ['nullable', 'boolean'],
            // Does tax apply to the product.
            'tax' => ['nullable', 'boolean'],
            // Should the product be featured in the Product Group.
            'isFeatured' => ['nullable', 'boolean'],
            // Is pro-rata billing enabled for this product.
            'proratabilling' => ['nullable', 'boolean'],
            // The description of the product to show on the product listing in the cart
            'description' => ['nullable', 'string'],
            // The id of the Email Template to use as the welcome email. Product/Service Messages only
            'welcomeemail' => ['nullable', 'integer'],
            // See https://docs.whmcs.com/Products_and_Services#Pricing_Tab
            'proratadate' => ['nullable', 'integer'],
            // See https://docs.whmcs.com/Products_and_Services#Pricing_Tab
            'proratachargenextmonth' => ['nullable', 'integer'],
            // A comma separated list of subdomains to offer on the domain register page. eg: .domain1.com,.domain2.com
            'subdomain' => ['nullable', 'string'],
            // When should the product be automatically setup. One of “ (never), ‘on’ (pending order), ‘payment’ (on payment), ‘order’ (on order)
            'autosetup' => ['nullable', 'string'],
            // The server module system name to associate with the product. eg: cpanel, autorelease, plesk
            'module' => ['nullable', 'string'],
            // The server group id used on product creation to associate an appropriate server
            'servergroupid' => ['nullable', 'integer'],
            // The first module configuration value
            'configoption1' => ['nullable', 'string'],
            // The second module configuration value
            'configoption2' => ['nullable', 'string'],
            // The third module configuration value
            'configoption3' => ['nullable', 'string'],
            // The fourth module configuration value
            'configoption4' => ['nullable', 'string'],
            // The fifth module configuration value
            'configoption5' => ['nullable', 'string'],
            // The sixth module configuration value
            'configoption6' => ['nullable', 'string'],
            // The order to in which to display on the order form
            'order' => ['nullable', 'integer'],
            // The pricing array to associate with the product. Format: $pricing[currencyid][cycle]. See Example.
            'pricing' => ['nullable', 'array'],
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $name = $request->input('name');
        $gid = $request->input('gid');
        $type = $request->input('type');
        $stockcontrol = $request->input('stockcontrol');
        $qty = $request->input('qty');
        $paytype = $request->input('paytype');
        $hidden = $request->input('hidden');
        $showdomainoptions = $request->input('showdomainoptions');
        $tax = $request->input('tax');
        $isFeatured = $request->input('isFeatured');
        $proratabilling = $request->input('proratabilling');
        $description = $request->input('description');
        $welcomeemail = $request->input('welcomeemail');
        $proratadate = $request->input('proratadate');
        $proratachargenextmonth = $request->input('proratachargenextmonth');
        $subdomain = $request->input('subdomain') ?? "";
        $autosetup = $request->input('autosetup') ?? "";
        $module = $request->input('module') ?? "";
        $servergroupid = $request->input('servergroupid') ?? 0;
        $configoption1 = $request->input('configoption1');
        $configoption2 = $request->input('configoption2');
        $configoption3 = $request->input('configoption3');
        $configoption4 = $request->input('configoption4');
        $configoption5 = $request->input('configoption5');
        $configoption6 = $request->input('configoption6');
        $order = $request->input('order');
        $pricing = $request->input('pricing') ?? [];

        if (!$name) {
            return ResponseAPI::Error([
                'message' => "You must supply a name for the product",
            ]);
        }
        if (!$type) {
            $type = "other";
        }
        if ($stockcontrol || $qty) {
            $stockcontrol = "1";
        } else {
            $stockcontrol = "0";
        }
        if (!$paytype) {
            $paytype = "free";
        }

        $product = new \App\Models\Product();
        $product->type = $type;
        $product->gid = $gid;
        $product->name = $name;
        $product->description = \App\Helpers\Sanitize::decode($description);
        $product->hidden = (int) (bool) $hidden;
        $product->showDomainOptions = (int) (bool)  $showdomainoptions;
        $product->welcomeemail  = (int) $welcomeemail;
        $product->stockcontrol = (bool)$stockcontrol;
        $product->qty = (int) $qty;
        $product->proratabilling  = (bool) $proratabilling;
        $product->proratadate   = (int) $proratadate;
        $product->proratachargenextmonth    = (int) $proratachargenextmonth;
        $product->paytype     = $paytype;
        $product->subdomain      = $subdomain;
        $product->autoSetup       = $autosetup;
        $product->servertype        = $module;
        $product->servergroup       = $servergroupid;;
        $product->configoption1 =  $configoption1 ?? '';
        $product->configoption2 = $configoption2 ?? '';
        $product->configoption3 = $configoption3 ?? '';
        $product->configoption4 = $configoption4 ?? '';
        $product->configoption5 = $configoption5 ?? '';
        $product->configoption6 = $configoption6 ?? '';
        $product->configoption7 = $configoption7 ?? '';
        $product->configoption8 = $configoption8 ?? '';
        $product->configoption9 = $configoption9 ?? '';
        $product->configoption10 = $configoption10 ?? '';
        $product->tax = (int) (bool)$tax;
        $product->order = (int)$order;
        $product->is_featured  = (int)$isFeatured;
        $product->save();
        $pid = $product->id;
        
        $pricing = (array) $pricing;
        foreach($pricing as $currency => $values){
            $price=new \App\Models\Pricing();
            $price->type='product';
            $price->currency=$currency;
            $price->relid=$pid;
            $price->msetupfee=$values["msetupfee"] ?? 0;
            $price->qsetupfee=$values["qsetupfee"] ?? 0;
            $price->ssetupfee=$values["ssetupfee"] ?? 0;
            $price->asetupfee=$values["asetupfee"] ?? 0;
            $price->bsetupfee=$values["bsetupfee"] ?? 0;
            $price->tsetupfee=$values["tsetupfee"] ?? 0;
            $price->monthly=$values["monthly"] ?? 0;
            $price->quarterly=$values["quarterly"] ?? 0;
            $price->semiannually=$values["semiannually"] ?? 0;
            $price->annually=$values["annually"] ?? 0;
            $price->biennially=$values["biennially"] ?? 0;
            $price->triennially=$values["triennially"] ?? 0;
            $price->save();
        }
        return  ResponseAPI::Success(["pid" => $pid]); 
    }

}
