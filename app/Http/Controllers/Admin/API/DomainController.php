<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator, DB;
use App\Helpers\ResponseAPI;

class DomainController extends Controller
{
    //
    public function addnewTld(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'tld' => ['required', 'string', 'starts_with:.', 'unique:App\Models\Domainpricing,extension'],
            ];
        
            $validator = Validator::make($request->all(), $rules);
        
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseAPI::Error([
                    'message' => $error,
                ]);
            }

            $newtld = trim($request->input("tld"));
            $newdns = (bool) $request->input("dns");
            $newemail = (bool) $request->input("email");
            $newidprot = (bool) $request->input("idprot");
            $neweppcode = (bool) $request->input("eppcode");
            $newautoreg = $request->input("auto_registration") ?? "";
            $tldGroup = $request->input("label");

            $domainsSetup = new \App\Helpers\DomainsHelper();
            $domainsSetup->addTld($newtld, $newdns, $newemail, $newidprot, $neweppcode, $newautoreg, -1, $tldGroup);
            \App\Helpers\Hooks::run_hook("TopLevelDomainAdd", array("tld" => $newtld, "supportsDnsManagement" => (bool) $newdns, "supportsEmailForwarding" => (bool) $newemail, "supportsIdProtection" => (bool) $newidprot, "requiresEppCode" => (bool) $neweppcode, "automaticRegistrar" => $newautoreg));

            DB::commit();
            return ResponseAPI::Success([
                'message' => "Your changes have been saved.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    public function deleteTld(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'id' => ['required', 'integer', 'exists:App\Models\Domainpricing,id'],
            ];
        
            $validator = Validator::make($request->all(), $rules);
        
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseAPI::Error([
                    'message' => $error,
                ]);
            }
            $id = $request->input("id");

            $domainTld = \App\Models\Domainpricing::find($id);
            $extension = $domainTld->extension;
            \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing TLD Removed: '" . $extension . "'");
            $domainTld->delete();
            foreach (array("domainregister", "domaintransfer", "domainrenew") as $type) {
                \App\Models\Pricing::where(array("type" => $type, "relid" => $id))->delete();
            }
            $spotlightTlds = \App\Helpers\Cfg::getValue("SpotlightTLDs");
            if ($spotlightTlds) {
                $spotlightTlds = explode(",", $spotlightTlds);
                if (in_array($extension, $spotlightTlds)) {
                    $spotlightTlds = array_flip($spotlightTlds);
                    unset($spotlightTlds[$extension]);
                    $spotlightTlds = array_flip($spotlightTlds);
                    \App\Helpers\Cfg::set("SpotlightTLDs", implode(",", $spotlightTlds));
                }
            }
            $whoisTlds = \App\Models\DomainLookupConfiguration::whereRegistrar("WhmcsWhois")->whereSetting("suggestTlds")->first();
            if ($whoisTlds) {
                $tlds = explode(",", $whoisTlds->value);
                if (in_array($extension, $tlds)) {
                    $tlds = array_flip($tlds);
                    unset($tlds[$extension]);
                    $tlds = array_flip($tlds);
                    $whoisTlds->value = implode(",", $tlds);
                    $whoisTlds->save();
                }
            }
            \App\Helpers\Hooks::run_hook("TopLevelDomainDelete", array("tld" => $extension));

            DB::commit();
            return ResponseAPI::Success([
                'message' => "Extension deleted.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    public function saveorder(Request $request)
    {
        DB::beginTransaction();
        try {
            $pricing = $request->input("pricing");
            foreach ($pricing as $order => $tldId) {
                \App\Models\Extension::where("id", $tldId)->update(array("order" => $order));
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing TLD Order Updated");

            DB::commit();
            return ResponseAPI::Success([
                'message' => "Sorting updated",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            $tld = $request->input("tld");
            $dns = $request->input("dns");
            $email = $request->input("email");
            $idprot = $request->input("idprot");
            $eppcode = $request->input("eppcode");
            $autoreg = $request->input("autoreg");
            $tldGroup = $request->input("tldGroup");
            $customGracePeriod = $request->input("grace");
            $gracePeriodFee = $request->input("grace_fee");
            $customRedemptionGracePeriod = $request->input("redemption");
            $redemptionGracePeriodFee = $request->input("redemption_grace_fee");
            $modifiedTlds = array();
            foreach ($tld as $id => $extension) {
                $domainTld = \App\Models\Extension::find($id);
                $extension = trim(strtolower($extension));
                $gracePeriod = $customGracePeriod[$id];
                $tldGracePeriodFee = $gracePeriodFee[$id];
                $redemptionGracePeriod = $customRedemptionGracePeriod[$id];
                $tldRedemptionGracePeriodFee = $redemptionGracePeriodFee[$id];
                if (!$gracePeriod && $gracePeriod !== "0" || $gracePeriod < 0) {
                    $gracePeriod = -1;
                }
                if ($tldGracePeriodFee === "" || $tldGracePeriodFee < -1) {
                    $tldGracePeriodFee = -1;
                }
                if (!$redemptionGracePeriod && $redemptionGracePeriod !== "0" || $redemptionGracePeriod < 0) {
                    $redemptionGracePeriod = -1;
                }
                if ($tldRedemptionGracePeriodFee === "" || $tldRedemptionGracePeriodFee < -1) {
                    $tldRedemptionGracePeriodFee = -1;
                }
                $changed = false;
                if ($domainTld->extension != $extension) {
                    $newExtension = \App\Models\Extension::where("extension", $extension)->first();
                    if ($newExtension && $newExtension->id != $id) {
                        return ResponseAPI::Error(['message' => str_replace("%s", $extension, \Lang::get("admin.domainsextensionalreadyexist"))]);
                    }
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing TLD Modified: '" . $domainTld->extension . "' to '" . $extension . "'");
                    $domainTld->extension = $extension;
                    if (!in_array($extension, $modifiedTlds)) {
                        $modifiedTlds[] = $extension;
                    }
                    $changed = true;
                }
                if ($domainTld->supportsDnsManagement != (bool) $dns[$id] || $domainTld->supportsEmailForwarding != (bool) $email[$id] || $domainTld->supportsIdProtection != (bool) $idprot[$id] || $domainTld->requiresEppCode != (bool) $eppcode[$id] || $domainTld->autoRegistrationRegistrar != $autoreg[$id] || $domainTld->group != $tldGroup[$id] || $domainTld->gracePeriod != $gracePeriod || $domainTld->gracePeriodFee != $tldGracePeriodFee || $domainTld->redemptionGracePeriod != $redemptionGracePeriod || $domainTld->redemptionGracePeriodFee != $tldRedemptionGracePeriodFee) {
                    $domainTld->supportsDnsManagement = (bool) $dns[$id];
                    $domainTld->supportsEmailForwarding = (bool) $email[$id];
                    $domainTld->supportsIdProtection = (bool) $idprot[$id];
                    $domainTld->requiresEppCode = (bool) $eppcode[$id];
                    $domainTld->autoRegistrationRegistrar = $autoreg[$id];
                    $domainTld->group = $tldGroup[$id];
                    $domainTld->gracePeriod = $gracePeriod;
                    $domainTld->gracePeriodFee = $tldGracePeriodFee;
                    $domainTld->redemptionGracePeriod = $redemptionGracePeriod;
                    $domainTld->redemptionGracePeriodFee = $tldRedemptionGracePeriodFee;
                    \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing Options Modified: '" . $extension . "'");
                    if (!in_array($extension, $modifiedTlds)) {
                        $modifiedTlds[] = $extension;
                    }
                    $changed = true;
                }
                if ($changed) {
                    $domainTld->save();
                }
            }
            \App\Helpers\Hooks::run_hook("TopLevelDomainUpdate", array("modifiedTlds" => $modifiedTlds));

            DB::commit();
            return ResponseAPI::Success([
                'message' => "Your changes have been saved.",
                // 'data' => $request->all(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    public function togglePremium(Request $request)
    {
        DB::beginTransaction();
        try {
            $enable = (int) $request->input("enable");
            $config = \App\Helpers\Cfg::set("PremiumDomains", $enable);

            DB::commit();
            return ResponseAPI::Success([
                'message' => "Updated",
                'data' => \App\Helpers\Cfg::get("PremiumDomains"),
                'enable' => $enable,
                'config' => $config,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    public function showduplicatetld(Request $request)
    {
        $tldsresult = \App\Models\Extension::all();
        $tldoptions = "<option value=\"\">" . \Lang::get("admin.domainsselecttldtoduplicate") . "</option>";
        foreach ($tldsresult as $key => $tldsdata) {
            $tldoptions .= "<option value=\"" . $tldsdata["extension"] . "\">" . $tldsdata["extension"] . "</option>";
        }
        $body = "<form method=\"post\" id=\"duplicatetldform\" action=\"" . route('apiconsumer.admin.setup.duplicatetld') . "\" onsubmit=\"$('#btnDuplicateTld').trigger('click'); return false;\">" . csrf_field() . "<table width=\"80%\" align=\"center\">" . "<tr><td>Existing TLD:</td><td><input type=\"hidden\" name=\"action\" value=\"duplicatetld\" /><select name=\"tld\" class=\"form-control\" required>" . $tldoptions . "</select></td></tr>" . "<tr><td>New TLD:</td><td><input type=\"text\" name=\"newtld\" class=\"form-control input-100\" required /></td></tr></table></form>";

        return ResponseAPI::Success([
            'body' => $body,
        ]);
    }

    public function duplicatetld(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'tld' => ['required', 'string', 'starts_with:.', 'exists:App\Models\Domainpricing,extension'],
                'newtld' => ['required', 'string', 'starts_with:.', 'unique:App\Models\Domainpricing,extension'],
            ];
        
            $validator = Validator::make($request->all(), $rules, [
                'tld.starts_with' => "TLD must start with dot (.)",
                'newtld.starts_with' => "TLD must start with dot (.)",
            ]);
        
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseAPI::Error([
                    'errorMsg' => $error,
                ]);
            }

            $tld = $request->input('tld');
            $newtld = $request->input('newtld');
            $newtld = trim($newtld);
            if (substr($newtld, 0, 1) != ".") {
                $newtld = "." . $newtld;
            }
            $tlddata = \App\Models\Domainpricing::where(array("extension" => $tld))->first();
            $tlddata = $tlddata->toArray();
            $relid = $tlddata["id"];
            $newtlddata = array();
            $newtlddata["extension"] = $newtld;
            $newtlddata["dnsmanagement"] = $tlddata["dnsmanagement"];
            $newtlddata["emailforwarding"] = $tlddata["emailforwarding"];
            $newtlddata["idprotection"] = $tlddata["idprotection"];
            $newtlddata["eppcode"] = $tlddata["eppcode"];
            $newtlddata["autoreg"] = $tlddata["autoreg"];
            $newtlddata["order"] = \App\Models\Domainpricing::max('order') + 1;
            $newrelid = \App\Models\Domainpricing::insertGetId($newtlddata);
            $regpricingresult = \App\Models\Pricing::where(array("relid" => $relid, "type" => "domainregister"))->get();
            foreach ($regpricingresult->toArray() as $regpricingdata) {
                unset($regpricingdata["id"]);
                $regpricingdata["relid"] = $newrelid;
                \App\Models\Pricing::insert($regpricingdata);
            }
            $transferpricingresult = \App\Models\Pricing::where(array("relid" => $relid, "type" => "domaintransfer"))->get();
            foreach ($transferpricingresult->toArray() as $transferpricingdata) {
                unset($transferpricingdata["id"]);
                $transferpricingdata["relid"] = $newrelid;
                \App\Models\Pricing::insert($transferpricingdata);
            }
            $renewpricingresult = \App\Models\Pricing::where(array("relid" => $relid, "type" => "domainrenew"))->get();
            foreach ($renewpricingresult->toArray() as $renewpricingdata) {
                unset($renewpricingdata["id"]);
                $renewpricingdata["relid"] = $newrelid;
                \App\Models\Pricing::insert($renewpricingdata);
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing TLD Duplicated: " . $tld . " to " . $newtld);
            \App\Helpers\Hooks::run_hook("TopLevelDomainAdd", array("tld" => $newtlddata["extension"], "supportsDnsManagement" => (bool) $newtlddata["dnsmanagement"], "supportsEmailForwarding" => (bool) $newtlddata["emailforwarding"], "supportsIdProtection" => (bool) $newtlddata["idprotection"], "requiresEppCode" => (bool) $newtlddata["eppcode"], "automaticRegistrar" => $newtlddata["autoreg"]));
            
            DB::commit();
            return ResponseAPI::Success([
                'successMsg' => "Duplicated",
                "dismiss" => true,
                "reloadPage" => true,
                // "body" => "<script type=\"text/javascript\">window.location.replace(\"configdomains.php?success=true\");</script>",
            ]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return ResponseAPI::Error(['errorMsg' => $e->getMessage()]);
        }
    }

    public function premiumlevels(Request $request)
    {
        $saveOutput = array();
        // save
        if ($request->has("save")) {
            // validator
            $rules = [
                'ids' => ['nullable', 'array'],
                'to' => ['required', 'array'],
                'markup' => ['required', 'array'],
            ];
        
            $validator = Validator::make($request->all(), $rules);
        
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                return ResponseAPI::Error([
                    'errorMsg' => $error,
                ]);
            }

            $ids = $request->input("ids") ?? [];
            $tos = $request->input("to") ?? [];
            $markups = $request->input("markup") ?? [];
            DB::beginTransaction();
            try {
                $message = "";
                $saved = $new = $toSave = false;
                foreach ($ids as $id) {
                    $level = \App\Models\DomainpricingPremium::find($id);
                    if ($level->to_amount != (double) $tos[$id]) {
                        $level->to_amount = (double) $tos[$id];
                        $toSave = true;
                    }
                    if ($level->markup != (double) $markups[$id]) {
                        $level->markup = (double) $markups[$id];
                        $toSave = true;
                    }
                    if ($toSave) {
                        $level->save();
                        $saved = true;
                    }
                }
                if ($saved) {
                    $message .= \Lang::get("admin.changesuccessdesc");
                }
                foreach ($tos["new"] as $key => $to) {
                    if (!$to) {
                        continue;
                    }
                    $level = new \App\Models\DomainpricingPremium();
                    $level->to_amount = (double) $to;
                    $level->markup = (double) $markups["new"][$key];
                    $level->save();
                    $new = true;
                }
                if ($new) {
                    $message .= "<br />" . \Lang::get("admin.changesuccessadded");
                }
                DB::commit();
                $saveOutput["successMsg"] = $message;
                $saveOutput["successMsgTitle"] = \Lang::get("admin.success");
            } catch (\Exception $e) {
                DB::rollBack();
                $saveOutput["errorMsg"] = $e->getMessage();
                $saveOutput["errorMsgTitle"] = \Lang::get("admin.error");
            }
        }
        $premiumBandsInformation = \Lang::get("admin.domainspremiumBandsInformation");
        $output = "<div class=\"alert alert-warning text-center\">
            " . $premiumBandsInformation . "
        </div>
        <form action=\"".route('apiconsumer.admin.setup.premium-levels')."\">
            " . csrf_field() . "
            <input type=\"hidden\" name=\"action\" value=\"premium-levels\" />
            <input type=\"hidden\" name=\"save\" value=\"true\" />
            <div class=\"table-responsive\">
                <table class=\"table\">
                    <tr>
                        <th>Price &lt;</th><th>Markup %</th><th></th>
                    </tr>";
        $maxLevel = NULL;
        $maxAmount = 0;
        $uniqueText = \Lang::get("admin.domainslevelUnique");
        
        foreach (\App\Models\DomainpricingPremium::all() as $premiumLevel) {
            if ($premiumLevel->to_amount == -1) {
                $maxLevel = $premiumLevel;
                continue;
            }
            if ($maxAmount < $premiumLevel->to_amount) {
                $maxAmount = $premiumLevel->to_amount;
            }
            $markup = floatval($premiumLevel->markup);
            $output .= "<tr>
                <input type=\"hidden\" name=\"ids[]\" value=\"" . $premiumLevel->id . "\" />
                <td>
                    <input type=\"text\" class=\"form-control to-amount\" name=\"to[" . $premiumLevel->id . "]\" value=\"" . $premiumLevel->to_amount . "\" data-toggle=\"tooltip\" data-placement=\"top\" data-trigger=\"manual\" title=\"" . $uniqueText . "\" />
                </td>
                <td>
                    <div class=\"input-group\">
                        <input type=\"text\" class=\"form-control\" name=\"markup[" . $premiumLevel->id . "]\" value=\"" . $markup . "\" placeholder=\"%\" />
                        <div class=\"input-group-append\"><span class='input-group-text'>%</span></div>
                    </div>
                </td>
                <td><a href=\"#\" onclick=\"return false;\" class=\"btn btn-sm premium-delete\" data-pricing-id=\"" . $premiumLevel->id . "\"><i class=\"fas fa-minus-circle text-danger\"></i></a></td>
            </tr>";
        }
        if ($maxLevel) {
            $markup = floatval($maxLevel->markup);
            $output .= "<tr>
                <input type=\"hidden\" name=\"ids[]\" value=\"" . $maxLevel->id . "\" />
                <td>
                    <input type=\"text\" class=\"form-control max-amount\" disabled=\"disabled\" value=\">= " . $maxAmount . "\" />
                    <input type=\"hidden\" name=\"to[" . $maxLevel->id . "]\" value=\"-1\" />
                </td>
                <td>
                    <div class=\"input-group\">
                        <input type=\"text\" class=\"form-control\" name=\"markup[" . $maxLevel->id . "]\" value=\"" . $markup . "\" placeholder=\"%\" />
                        <div class=\"input-group-addon\">%</div>
                    </div>
                </td>
                <td></td>
            </tr>";
        }
        $output .= "<tr><td colspan=\"3\"></td></tr>
                    <tr class=\"new\">
                        <td>
                            <input type=\"text\" class=\"form-control to-amount\" name=\"to[new][]\" value=\"\" placeholder=\"New Price <\" data-toggle=\"tooltip\" data-placement=\"top\" data-trigger=\"manual\" title=\"" . $uniqueText . "\" />
                        </td>
                        <td>
                            <div class=\"input-group\">
                                <input type=\"text\" class=\"form-control\" name=\"markup[new][]\" value=\"\" placeholder=\"New Markup %\" />
                                <div class=\"input-group-append\"><span class='input-group-text'>%</span></div>
                            </div>
                        </td>
                        <td class=\"remove-clone\">
                            <a href=\"#\" onclick=\"return false;\" class=\"btn btn-sm add-more-new\">
                                <i class=\"fas fa-plus-circle text-success\"></i>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <script type=\"text/javascript\">
            $(document).ready(function() {
                $(document).off('change blur keyup', '.to-amount');
                $(document).on('change blur keyup', '.to-amount', function() {
                    var amounts = [];
                    $('.to-amount').not($(this)).each(function () {
                        amounts.push(parseFloat($(this).val()).toFixed(2));
                    });
                    if ($.inArray(parseFloat($(this).val()).toFixed(2), amounts) >= 0) {
                        $('#btnSavePremium').attr('disabled', 'disabled').addClass('disabled');
                        $(this).focus();
                        $(this).tooltip('show');
                    } else {
                        $('#btnSavePremium').removeAttr('disabled').removeClass('disabled');
                        $(this).tooltip('hide');
                    }
                });
            
                $(document).off('click', '.premium-delete');
                $(document).on('click', '.premium-delete', function() {
                    var self = $(this);
                    self.attr('disabled', 'disabled').addClass('disabled');
                    $.ajax({
                        url: route('apiconsumer.admin.setup.delete-premium'),
                        type: 'POST',
                        data: {_token: '".csrf_token()."', id: parseInt(self.data('pricing-id')), action: 'delete-premium'},
                        success: function(data) {
                            if (data.successMsg) {
                                // $.growl.notice({ title: data.successMsgTitle, message: data.successMsg });
                                $.notify(data.successMsg, 'success');
                                self.parents('tr').slideUp('fast').remove();
                                var maxValue = 0.00;
                                $('.to-amount').each(function () {
                                    if (parseFloat($(this).val()) > maxValue) {
                                        maxValue = parseFloat($(this).val());
                                    }
                                });
                                $('.max-amount').val('>= ' + maxValue.toFixed(2));
                            } else {
                                // $.growl.warning({ title: data.errorMsgTitle, message: data.errorMsg });
                                $.notify(data.errorMsg, 'error');
                                self.removeAttr('disabled').removeClass('disabled');
                            }
                        },
                    });
                });
                $(document).off('click', '.add-more-new');
                $(document).on('click', '.add-more-new', function() {
                    $('tr.new').clone().appendTo($(this).parents('table')).removeClass('new')
                        .find('.remove-clone').html('').end()
                        .find('input').val('').end();
                });
            });
        </script>";

        return ResponseAPI::Success(array_merge([
            'body' => $output,
        ], $saveOutput));
    }

    public function deletepremium(Request $request)
    {
        $id = (int) $request->input("id");
        try {
            \App\Models\DomainpricingPremium::where("id", "=", $id)->delete();
        } catch (\Exception $e) {
            return ResponseAPI::Error(array("errorMsg" => $e->getMessage(), "errorMsgTitle" => \Lang::get("admin.error")));
        }
        return ResponseAPI::Success(array("successMsg" => \Lang::get("admin.changesuccessdeleted"), "successMsgTitle" => \Lang::get("admin.success")));
    }

    public function lookupprovider(Request $request)
    {
        $registrarProviders = \App\Helpers\Domains\DomainLookup\Provider::getAvailableRegistrarProviders();
        if ($request->has("provider")) {
            $premiumSupport = false;
            $imgPath = url('/');
            if (array_key_exists($request->input("provider"), $registrarProviders)) {
                $premiumSupport = true;
                \App\Helpers\Cfg::set("domainLookupProvider", "Registrar");
                \App\Helpers\Cfg::set("domainLookupRegistrar", $request->input("provider"));
                $thisProvider = $registrarProviders[$request->input("provider")];
                if ($thisProvider["logo"]) {
                    $lookupRegistrar = "<img class=\"img-fluid mb-3\" id=\"imgLookupRegistrar\" src=\"" . $thisProvider["logo"] . "\">";
                } else {
                    $lookupRegistrar = $thisProvider["name"];
                }
                if (!$thisProvider["suggestionSettings"]) {
                    return ResponseAPI::Success(array("successMsg" => \Lang::get("admin.changesuccess"), "successMsgTitle" => \Lang::get("admin.success"), "logo" => $lookupRegistrar, "dismiss" => true, "premiumSupport" => $premiumSupport));
                }
            } else {
                if ($request->input("provider") == "WhmcsDomains") {
                    \App\Helpers\Cfg::set("domainLookupProvider", "WhmcsDomains");
                    \App\Helpers\Cfg::set("domainLookupRegistrar", "");
                    $lookupRegistrar = "<img src=\"" . $imgPath . "/lookup/whmcs-namespinning-large.png\" />";
                } else {
                    \App\Helpers\Cfg::set("domainLookupProvider", "WhmcsWhois");
                    \App\Helpers\Cfg::set("domainLookupRegistrar", "");
                    $lookupRegistrar = "<img src=\"" . $imgPath . "/lookup/standard-whois.png\">";
                }
            }
            return ResponseAPI::Success(array("successMsg" => \Lang::get("admin.changesuccess"), "successMsgTitle" => \Lang::get("admin.success"), "logo" => $lookupRegistrar, "url" => route('apiconsumer.admin.setup.domainlookup.index', 'action=configure'), "title" => "Configure Lookup Provider", "submitlabel" => \Lang::get("admin.save"), "submitId" => "btnSaveLookupConfiguration", "premiumSupport" => $premiumSupport));
        }
        $primaryProviders = array(
            "whmcsdomains" => array(
                "name" => "WhmcsDomains",
                "logo" => "/lookup/whmcs-namespinning.png",
                "description" => "Fastest lookup times with high quality and relevant name suggestions accross multiple TLDs + multi-language support.",
                "recommended" => true,
                // "displayName" => "",
            ),
            "whois" => array(
                "name" => "WhmcsWhois",
                // "logo" => "",
                "displayName" => "Standard WHOIS",
                "description" => "Domain availability checks using the standard WHOIS protocol.<br><br>Provides results for the requested TLD along with other TLDs you select, but no automated SLD suggestions.",
                "recommended" => false,
            ),
            "registrar" => array(
                "name" => "Registrar",
                // "logo" => "",
                "displayName" => "Domain Registrar",
                "description" => "Use a domain registrar of your choice to perform domain availability checks.<br><br>Features vary depending upon the registrar being used.",
                "recommended" => false,
            ),
        );
        $output = array();
        foreach ($primaryProviders as $provider) {
            $isActive =\App\Helpers\Cfg::getValue("domainLookupProvider") == $provider["name"];
            $output[] = "<div class=\"col-sm-4\">
                <div class=\"lookup-provider bordered rounded p-3 h-100" . ($isActive ? " active" : "") . "\" data-provider=\"" . $provider["name"] . "\" >" . "<div class=\"logo\">" . (isset($provider["logo"]) ? "<img src=\"" . asset($provider["logo"]) . "\" class=\"img-fluid\">" : "<h2>" . $provider["displayName"] . "</h2>") . "</div>" . ($provider["recommended"] ? "<span class=\"badge badge-info\">Recommended</span>" : "") . "<p>" . $provider["description"] . "</p>" . "<button class=\"btn btn-light btn-sm\">Select</button>" . "</div></div>";
        }
        $registrarsOutput = array();
        foreach ($registrarProviders as $registrarName => $registrarProvider) {
            $registrarsOutput[] = "<li role=\"presentation\" class=\"nav-item " . ($registrarName ==\App\Helpers\Cfg::getValue("domainLookupRegistrar") ? "active" : "") . "\"><a class=\"nav-link border rounded ".($registrarName ==\App\Helpers\Cfg::getValue("domainLookupRegistrar") ? "active" : "")."\" href=\"#\" data-provider=\"" . $registrarName . "\">" . $registrarProvider["name"] . "</a></li>";
        }
        $output = "<div class=\"row row-lookup-providers text-center\">" . implode($output) . "</div>
        <div class=\"lookup-providers-registrars mt-3 p-2" . (\App\Helpers\Cfg::getValue("domainLookupProvider") == "Registrar" ? "" : " d-none") . "\">
            <h5>Choose a registrar...</h5>
            <ul class=\"nav nav-pills\">
                " . implode($registrarsOutput) . "
            </ul>
        </div>" . "<script>
        $(document).ready(function() {
            $(document).off('click', '.lookup-provider, .lookup-providers-registrars a');
            $(document).on('click', '.lookup-provider, .lookup-providers-registrars a', function() {
        
                var self = $(this);
                var provider = self.data('provider');
        
                $('.lookup-provider').removeClass('active');
                self.addClass('active');
        
                if (provider == 'Registrar') {
                    if ($('.lookup-providers-registrars').hasClass('d-none')) {
                        $('.lookup-providers-registrars').hide().removeClass('d-none');
                    }
                    $('.lookup-providers-registrars').slideDown();
                    return;
                }
        
                $.ajax({
                    url: route('apiconsumer.admin.setup.lookup-provider'),
                    type: 'POST',
                    data: {_token: '".csrf_token()."', provider: provider, action: 'lookup-provider'},
                    success: function(data) {
                        if (data.successMsg) {
                            $('.selected-provider').html(data.logo);
                            updateAjaxModal(data);
                            var toggle = $('.premium-toggle-switch');
                            if (data.premiumSupport) {
                                // toggle.bootstrapSwitch('disabled', false);
                                toggle.attr('disabled', false);
                            } else {
                                // toggle.bootstrapSwitch('state', false);
                                // toggle.bootstrapSwitch('disabled', true);
                                toggle.prop('checked', false);
                                toggle.attr('disabled', true);
                            }
                        } else {
                            // jQuery.growl.warning({ title: data.errorMsgTitle, message: data.errorMsg });
                            $.notify(data.errorMsg, 'error');
                        }
                    },
                });
            });
        });
        </script>";
        
        return ResponseAPI::Success([
            "body" => $output,
        ]);
    }

    public function saveaddons(Request $request)
    {
        DB::beginTransaction();
        try {
            $currency = $request->input('currency') ?? [];
            foreach ($currency as $currency_id => $pricing) {
                \App\Models\Pricing::where(array("type" => "domainaddons", "currency" => $currency_id, "relid" => 0))->update($pricing);
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Domain Pricing Addons Modified");
            
            DB::commit();
            return ResponseAPI::Success([
                'message' => "Your changes have been saved.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }
}
