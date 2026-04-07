<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;

use App\Helpers\ResponseAPI;
use App\Helpers\Database;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    /**
     * removeEmailAttachment
     */
    public function removeEmailTemplateAttachment(Request $request)
    {
        DB::beginTransaction();
        try {
            $emailtemplate = \App\Models\Emailtemplate::findOrFail($request->id);
            $attachments = $emailtemplate->attachments;

            if (($key = array_search($request->name, $attachments)) !== false) {
                unset($attachments[$key]);
            }

            $storage = Storage::disk('attachments')->delete($request->name);

            // update
            $attachments = implode(',', $attachments);
            DB::table(Database::prefix()."emailtemplates")->where("id", $request->id)->update(['attachments' => $attachments]);

            DB::commit();
            return ResponseAPI::Success();
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    /**
     * saveAddonsModuleConfig
     */
    public function saveAddonsModuleConfig(Request $request)
    {
        $datapost = $request->all();
        $access = $datapost['access'] ?? [];
        DB::beginTransaction();

        try {
            // $fields = $data['fields'];
            // $activemodules = array_filter(explode(",", $CONFIG["ActiveAddonModules"]));
            $addonsmodule = new \App\Module\Addons();
            $activemodules = [];
            foreach ($addonsmodule->getActiveModules() as $key => $module) {
                $module = $module;
                if ($addonsmodule->load($module)) {
                    $activemodules[] = $module;
                }
            }
            $addon_modules = $addonmodulehooks = array();
            $modules = \Module::toCollection();
            foreach ($modules as $key => $module) {
                if (strpos($module->getPath(), '/Addons') !== false) {
                    $addons = new \App\Module\Addons();
                    $addons->load($module->getLowerName());
                    $configarray = $addons->getConfig();
                    $addon_modules[$module->getLowerName()] = $configarray;
                }
            }
            ksort($addon_modules);

            $exvars = array();
            $result = \App\Models\AddonModule::all();
            foreach ($result->toArray() as $data) {
                $modName = $data["module"];
                $exvars[$modName][$data["setting"]] = $data["value"];
            }
            $changedPermissions = array();
            $adminRoleNames = array();
            foreach (\Spatie\Permission\Models\Role::where('guard_name', 'admin')->get() as $roleInfo) {
                $adminRoleNames[$roleInfo->id] = $roleInfo->name;
            }
            foreach ($activemodules as $module) {
                if (isset($access[$module])) {
                    \App\Models\AddonModule::where(array("setting" => "access", "module" => $module))->delete();
                }
                $existingAccess = isset($exvars[$module]["access"]) && ($exvars[$module]["access"] != "" || $exvars[$module]["access"] != null)? explode(",", $exvars[$module]["access"]) : array();
                $newAccess = isset($access[$module]) ? array_keys($access[$module]) : array();
                foreach ($newAccess as $roleId) {
                    if (!in_array($roleId, $existingAccess)) {
                        $changedPermissions[$addon_modules[$module]["name"]]["added"][] = $adminRoleNames[$roleId];
                    }
                }
                foreach ($existingAccess as $roleId) {
                    if (!in_array($roleId, $newAccess)) {
                        $changedPermissions[$addon_modules[$module]["name"]]["removed"][] = $adminRoleNames[$roleId];
                    }
                }
                // \App\Models\AddonModule::insert(array("module" => $module, "setting" => "access", "value" => implode(",", $newAccess)));
                if (isset($access[$module])) {
                    \App\Models\AddonModule::updateOrInsert(
                        ['module' => $module, 'setting' => 'access'],
                        ['value' => implode(",", $newAccess)]
                    );
                }
            }
            foreach ($changedPermissions as $module => $values) {
                $activity = array();
                if (isset($values["added"]) && array_filter($values["added"])) {
                    $activity[] = " Added Role Group(s): " . implode(", ", $values["added"]) . ".";
                }
                if (isset($values["removed"]) && array_filter($values["removed"])) {
                    $activity[] = " Removed Role Group(s): " . implode(", ", $values["removed"]) . ".";
                }
                if ($activity) {
                    \App\Helpers\AdminFunctions::logAdminActivity("Addon Module Access Permissions Changed - " . $module . " - " . implode("", $activity));
                }
            }
            $changedValues = array();
            foreach ($addon_modules as $module => $vals) {
                if (in_array($module, $activemodules)) {
                    foreach ($vals["fields"] as $key => $values) {
                        $valueToSave = "";
                        $fieldName = $values["FriendlyName"] ?: $key;
                        if (isset($datapost["fields"][$module][$key])) {
                            $valueToSave = trim(\App\Helpers\Sanitize::decode($datapost["fields"][$module][$key]));
                            if ($values["Type"] == "password") {
                                $updatedPassword = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($valueToSave, $exvars[$module][$key]);
                                if ($updatedPassword === false) {
                                    $valueToSave = $exvars[$module][$key];
                                }
                            }
                        } else {
                            if ($values["Type"] == "yesno") {
                                $valueToSave = "";
                            } else {
                                if (isset($values["Default"])) {
                                    $valueToSave = $values["Default"];
                                }
                            }
                        }
                        if ($values["Type"] == "yesno") {
                            $valueToSave = !empty($valueToSave) && $valueToSave != "off" && $valueToSave != "disabled" ? "on" : "";
                        }
                        if (isset($exvars[$module][$key])) {
                            if ($valueToSave != $exvars[$module][$key]) {
                                if ($values["Type"] == "password") {
                                    $changedValues[$vals["name"]][] = (string) $fieldName . " (password field) value changed.";
                                } else {
                                    $changedValues[$vals["name"]][] = (string) $fieldName . ": '" . $exvars[$module][$key] . "'" . " to '" . $valueToSave . "'";
                                }
                            }
                            \App\Models\AddonModule::where(array("module" => $module, "setting" => $key))->update(array("value" => $valueToSave));
                        } else {
                            if ($values["Type"] == "password") {
                                $changedValues[$vals["name"]][] = (string) $fieldName . " (password field) value set.";
                            } else {
                                $changedValues[$vals["name"]][] = "Initial setting of " . $fieldName . " to '" . $valueToSave . "'";
                            }
                            \App\Models\AddonModule::insert(array("module" => $module, "setting" => $key, "value" => $valueToSave));
                        }
                    }
                }
            }
            foreach ($changedValues as $changedModule => $changes) {
                if ($changes) {
                    \App\Helpers\AdminFunctions::logAdminActivity("Addon Module Settings Modified - " . $changedModule . "  - " . implode(", ", $changes));
                }
            }
            
            DB::commit();
            return ResponseAPI::Success([
                'message' => "Successfully Updated!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    /**
     * saveRegistrarModuleConfig
     */
    public function saveRegistrarModuleConfig(Request $request)
    {
        $datapost = $request->all();
        try {
            $module = $datapost['module'];
            $m = \Module::findOrFail($module);

            $registrar = new \App\Module\Registrar();
            if ($registrar->load($m->getLowerName())) {
                if ($registrar->isActivated()) {
                    $registrar->saveSettings($datapost);
                } else {
                    $registrar->activate()->saveSettings($datapost);
                }
                return ResponseAPI::Success([
                    'message' => "Successfully Updated!",
                ]);
            } else {
                return ResponseAPI::Error(['message' => 'Module not found']);
            }
        } catch (\Exception $e) {
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    /**
     * fetchModuleSettings
     */
    public function fetchModuleSettings(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');
        // $product = \App\Models\Product::find($productId);
        // dd($product);
        // if (!$product) {
        //     return ResponseAPI::Error([
        //         'message' => "Product Not Found",
        //     ]);
        // }
        switch ($type) {
            case 'configaddons':
                $setup = new \App\Helpers\AddonSetup();
                break;
            case 'configproducts':
                $setup = new \App\Helpers\ProductSetup();
                break;
            default:
                return ResponseAPI::Error([
                    'message' => "Invalid Config Type",
                ]);
                break;
        }

        try {
            $response = $setup->getModuleSettings($id);
            if (!is_array($response)) {
                return ResponseAPI::Error([
                    'message' => "Invalid response",
                ]);
            }

            return ResponseAPI::Success([
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
