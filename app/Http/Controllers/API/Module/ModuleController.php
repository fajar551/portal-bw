<?php

namespace App\Http\Controllers\API\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use Validator;

class ModuleController extends Controller
{
    //
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function ActivateModule()
    {
        $rules = [
            'moduleType' => ['required', 'string'],
            'moduleName' => ['required', 'string'],
            'parameters' => ['nullable', 'array'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first(),
            ]);
        }

        // vars
        $moduleType = $this->request->input('moduleType');
        $moduleName = $this->request->input('moduleName');
        $parameters = $this->request->input('parameters') ?? [];

        $supportedModuleTypes = array("gateway", "registrar", "addons", "fraud");
        if (!in_array($moduleType, $supportedModuleTypes)) {
            $apiresults = array("result" => "error", "message" => "Invalid module type provided. Supported module types include: " . implode(", ", $supportedModuleTypes));
            return ResponseAPI::Error($apiresults);
        }

        $moduleClassName = "\\App\\Module\\" . ucfirst($moduleType);
        $moduleInterface = new $moduleClassName();
        if (!in_array(strtolower($moduleName), array_keys($moduleInterface->getList()))) {
            $apiresults = array("result" => "error", "message" => "Invalid module name provided.");
            return ResponseAPI::Error($apiresults);
        }

        $moduleInterface->load($moduleName);
        try {
            if (!is_array($parameters)) {
                $parameters = array();
            }
            $moduleInterface->activate($parameters);
        } catch (\App\Exceptions\Module\NotImplemented $e) {
            $apiresults = array("result" => "error", "message" => "Module activation not supported by module type.");
            return ResponseAPI::Error($apiresults);
        } catch (\App\Exceptions\Module\NotActivated $e) {
            $apiresults = array("result" => "error", "message" => "Failed to activate: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        } catch (\Exception $e) {
            $apiresults = array("result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        }
        $apiresults = array("result" => "success");
        return ResponseAPI::Success($apiresults);
    }

    public function DeactivateModule()
    {
        $rules = [
            'moduleType' => ['required', 'string'],
            'moduleName' => ['required', 'string'],
            'newGateway' => ['nullable', 'string'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first(),
            ]);
        }

        // vars
        $moduleType = $this->request->input('moduleType');
        $moduleName = $this->request->input('moduleName');
        $newGateway = $this->request->input('newGateway');

        $supportedModuleTypes = array("gateway", "registrar", "addons", "fraud");
        if (!in_array($moduleType, $supportedModuleTypes)) {
            $apiresults = array("result" => "error", "message" => "Invalid module type provided. Supported module types include: " . implode(", ", $supportedModuleTypes));
            return ResponseAPI::Error($apiresults);
        }

        $moduleClassName = "\\App\\Module\\" . ucfirst($moduleType);
        $moduleInterface = new $moduleClassName();
        if (!in_array(strtolower($moduleName), array_keys($moduleInterface->getList()))) {
            $apiresults = array("result" => "error", "message" => "Invalid module name provided.");
            return ResponseAPI::Error($apiresults);
        }

        $moduleInterface->load($moduleName);
        try {
            $parameters = array("newGateway" => $newGateway);
            $moduleInterface->deactivate($parameters);
        } catch (\App\Exceptions\Module\NotImplemented $e) {
            $apiresults = array("result" => "error", "message" => "Module deactivation not supported by module type.");
            return ResponseAPI::Error($apiresults);
        } catch (\App\Exceptions\Module\NotActivated $e) {
            $apiresults = array("result" => "error", "message" => "Failed to deactivate: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        } catch (\Exception $e) {
            $apiresults = array("result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        }
        $apiresults = array("result" => "success");
        return ResponseAPI::Success($apiresults);
    }

    public function GetModuleConfigurationParameters()
    {
        $rules = [
            'moduleType' => ['required', 'string'],
            'moduleName' => ['required', 'string'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first(),
            ]);
        }

        // vars
        $moduleType = $this->request->input('moduleType');
        $moduleName = $this->request->input('moduleName');

        $supportedModuleTypes = array("gateway", "registrar", "addons", "fraud");
        if (!in_array($moduleType, $supportedModuleTypes)) {
            $apiresults = array("result" => "error", "message" => "Invalid module type provided. Supported module types include: " . implode(", ", $supportedModuleTypes));
            return ResponseAPI::Error($apiresults);
        }

        $moduleClassName = "\\App\\Module\\" . ucfirst($moduleType);
        $moduleInterface = new $moduleClassName();
        if (!in_array(strtolower($moduleName), array_keys($moduleInterface->getList()))) {
            $apiresults = array("result" => "error", "message" => "Invalid module name provided.");
            return ResponseAPI::Error($apiresults);
        }

        $paramsToReturn = array();
        $moduleInterface->load($moduleName);
        try {
            $configurationParams = $moduleInterface->getConfiguration();
            if (is_array($configurationParams)) {
                foreach ($configurationParams as $key => $values) {
                    if ($values["Type"] == "System") {
                        if ($key != "FriendlyName") {
                            continue;
                        }
                        $values["Type"] = "text";
                    }
                    $paramsToReturn[] = array("name" => $key, "displayName" => $values["FriendlyName"] ?? $key, "fieldType" => $values["Type"]);
                }
            }
        } catch (\App\Exceptions\Module\NotImplemented $e) {
            $apiresults = array("result" => "error", "message" => "Get module configuration parameters not supported by module type.");
            return ResponseAPI::Error($apiresults);
        } catch (\Exception $e) {
            $apiresults = array("result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        }
        $apiresults = array("result" => "success", "parameters" => $paramsToReturn);
        return ResponseAPI::Success($apiresults);
    }

    public function UpdateModuleConfiguration()
    {
        $rules = [
            'moduleType' => ['required', 'string'],
            'moduleName' => ['required', 'string'],
            'parameters' => ['nullable', 'array'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => $validator->errors()->first(),
            ]);
        }

        // vars
        $moduleType = $this->request->input('moduleType');
        $moduleName = $this->request->input('moduleName');
        $parameters = $this->request->input('parameters') ?? [];

        $supportedModuleTypes = array("gateway", "registrar", "addons", "fraud");
        if (!in_array($moduleType, $supportedModuleTypes)) {
            $apiresults = array("result" => "error", "message" => "Invalid module type provided. Supported module types include: " . implode(", ", $supportedModuleTypes));
            return ResponseAPI::Error($apiresults);
        }

        $moduleClassName = "\\App\\Module\\" . ucfirst($moduleType);
        $moduleInterface = new $moduleClassName();
        if (!in_array(strtolower($moduleName), array_keys($moduleInterface->getList()))) {
            $apiresults = array("result" => "error", "message" => "Invalid module name provided.");
            return ResponseAPI::Error($apiresults);
        }

        $moduleInterface->load($moduleName);
        try {
            if (!is_array($parameters)) {
                $parameters = array();
            }
            $moduleInterface->updateConfiguration($parameters);
        } catch (\App\Exceptions\Module\NotImplemented $e) {
            $apiresults = array("result" => "error", "message" => "Module activation not supported by module type.");
            return ResponseAPI::Error($apiresults);
        } catch (\App\Exceptions\Module\NotActivated $e) {
            $apiresults = array("result" => "error", "message" => "Failed to activate: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        } catch (\Exception $e) {
            $apiresults = array("result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage());
            return ResponseAPI::Error($apiresults);
        }
        $apiresults = array("result" => "success");
        return ResponseAPI::Success($apiresults);
    }
}
