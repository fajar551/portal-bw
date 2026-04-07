<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AddonAutomation
{
	protected $action = "";
    protected $addon = NULL;
    protected $aliasActions = array("CancelAccount" => "TerminateAccount", "Fraud" => "TerminateAccount");
    protected $error = "";
    protected $supportedActions = array("CreateAccount" => "AddonActivation", "SuspendAccount" => "AddonSuspended", "UnsuspendAccount" => "AddonUnsuspended", "TerminateAccount" => "AddonTerminated", "CancelAccount" => "AddonCancelled", "Fraud" => "AddonFraud", "Renew" => "", "ChangePassword" => "", "LoginLink" => "", "ChangePackage" => "", "CustomFunction" => "", "ClientArea" => "");
    public static function factory($addon)
    {
        $self = new self();
        if ($addon instanceof \App\Models\Hostingaddon) {
            $self->addon = $addon;
        } else {
            $self->addon = \App\Models\Hostingaddon::findOrFail($addon);
        }
        return $self;
    }
    protected function setAction($action)
    {
        $this->action = $action;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getError()
    {
        return $this->error;
    }
    protected function addError($error)
    {
        $this->error = $error;
    }
    public function runAction($action, $extra = "")
    {
        if (!array_key_exists($action, $this->supportedActions)) {
            throw new \App\Exceptions\Module\NotServicable("Invalid Action");
        }
        
        $this->setAction($action == "CustomFunction" ? $extra : $action);

        $m = new \App\Module\Server();
        switch ($action) {
            case "CustomFunction":
            case "SuspendAccount":
                $variables = array($this->addon->serviceId, $extra, $this->addon->id);
                $function = "Server" . $action;
                $result = $m->{$function}($variables[0], $variables[1], $variables[2]);
                break;
            default:
                $variables = array($this->addon->serviceId, $this->addon->id);
                $function = "Server" . $action;
                $result = $m->{$function}($variables[0], $variables[1]);
                break;
        }

        switch ($result) {
            case "success":
                break;
            default:
                $this->addError($result);
                return false;
        }
        $this->runHook();
        return true;
    }
    protected function runHook()
    {
        if ($this->supportedActions[$this->getAction()]) {
            \App\Helpers\Hooks::run_hook($this->supportedActions[$this->getAction()], array("id" => $this->addon->id, "userid" => $this->addon->clientId, "serviceid" => $this->addon->serviceId, "addonid" => $this->addon->addonId));
        }
    }
}
