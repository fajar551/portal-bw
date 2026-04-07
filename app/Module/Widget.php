<?php

namespace App\Module;

use Auth;
use App\Helpers\LogActivity;

class Widget extends AbstractModule
{
    protected $type = self::TYPE_WIDGET;
    protected $usesDirectories = false;
    protected $widgets = NULL;
    protected $hookName = "AdminHomeWidgets";
    public function loadWidgets()
    {
        $this->widgets = array();
        foreach ($this->getList() as $widget) {
            $widgetName = $widget->getName();
            $a[] = $widgetName;
            if (strtolower($widgetName) == "index") {
                continue;
            }
            try {
                $widgetClass = "\\Modules\\Widgets\\" . $widgetName . "\\Http\\Controllers\\" . $widgetName . "Controller";
                if (class_exists($widgetClass)) {
                    $widget = new $widgetClass();
                    if (!$widget->getRequiredPermission() || \App\Helpers\AdminFunctions::checkPermission($widget->getRequiredPermission(), true)) {
                        $this->widgets[] = new $widgetClass();
                    }
                }
            } catch (\Exception $e) {
                \Log::debug("An Error Occurred loading widget " . $widgetName . ": " . $e->getMessage());
                LogActivity::Save("An Error Occurred loading widget " . $widgetName . ": " . $e->getMessage());
            }
        }
        $this->loadWidgetsViaHooks();
        usort($this->widgets, function ($a, $b) {
            return $b->getWeight() < $a->getWeight();
        });
        return $this->widgets;
    }
    protected function initGlobalChartForLegacyWidgets()
    {
        global $chart;
        // if (!$chart instanceof \WHMCS\Chart) {
        //     $chart = new \WHMCS\Chart();
        // }
    }
    protected function loadWidgetsViaHooks()
    {
        $hooks = \App\Helpers\Hooks::get_registered_hooks($this->hookName);
        if (count($hooks) == 0) {
            return NULL;
        }
        $authadmin = Auth::guard('admin')->user();
        $adminid = $authadmin ? $authadmin->id : 0;
        // // $allowedwidgets = get_query_val("tbladmins", , , "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");
        // $allowedwidgets = \App\Models\Admin::selectRaw("tbladminroles.widgets")->where(array("tbladmins.id" => $adminid))->value("");
        // $allowedwidgets = explode(",", $allowedwidgets);
        $hookjquerycode = "";
        $args = array("adminid" => $adminid, "loading" => "<img src=\"images/loading.gif\" align=\"absmiddle\" /> " . \Lang::get("admin.globalloading"));
        $results = array();
        foreach ($hooks as $hook) {
            $widgetname = substr($hook["hook_function"], 7);
            if (is_callable($hook["hook_function"]) && (!$widgetname || in_array($widgetname, $allowedwidgets))) {
                try {
                    $this->initGlobalChartForLegacyWidgets();
                    $response = call_user_func($hook["hook_function"], $args);
                    $widget = null;
                    if ($response instanceof AbstractWidget) {
                        $widget = $response;
                    } else {
                        if (is_array($response)) {
                            $widget = LegacyWidget::factory($response["title"], $response["content"], $response["jscode"], $response["jquerycode"]);
                        }
                    }
                    if ($widget && (!$widget->getRequiredPermission() || \App\Helpers\AdminFunctions::checkPermission($widget->getRequiredPermission(), true))) {
                        $this->widgets[] = $widget;
                    }
                } catch (\Exception $e) {
                    LogActivity::Save("An Error Occurred loading widget " . $widgetname . ": " . $e->getMessage());
                } catch (\Error $e) {
                    LogActivity::Save("An Error Occurred loading widget " . $widgetname . ": " . $e->getMessage());
                }
            }
        }
    }
    public function getAllWidgets()
    {
        if (is_null($this->widgets)) {
            $this->loadWidgets();
        }
        return $this->widgets;
    }
    public function getWidgetByName($widgetId)
    {
        if (is_null($this->widgets)) {
            $this->loadWidgets();
        }
        foreach ($this->widgets as $widget) {
            if ($widget->getId() == $widgetId) {
                return $widget;
            }
        }
        throw new \Exception("Invalid widget name.");
    }
}
