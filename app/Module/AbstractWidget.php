<?php

namespace App\Module;

use Auth;
use Illuminate\Routing\Controller;

abstract class AbstractWidget extends Controller
{
    protected $title = NULL;
    protected $description = NULL;
    protected $columns = 1;
    protected $weight = 100;
    protected $wrapper = true;
    protected $cache = false;
    protected $cachePerUser = false;
    protected $cacheExpiry = 3600;
    protected $requiredPermission = "";
    protected $draggable = true;
    protected $adminUser = NULL;
    public function getId()
    {
        $class = str_replace("Modules\\Widgets\\", "", get_class($this));
        $class = explode("\\", $class);
        $class = array_key_exists(0, $class) ? $class[0] : Str::random(5);
        return $class;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getColumnSize()
    {
        return (int) $this->columns;
    }
    public function getWeight()
    {
        $weight = $this->weight;
        $widgetId = $this->getId();
        if (is_null($this->adminUser)) {
            $authadmin = Auth::guard('admin')->user();
            $adminid = $authadmin ? $authadmin->id : 0;
            $admin = \App\User\Admin::find($adminid);
            $this->adminUser = $admin ?? null;
        }
        if ($this->adminUser && $this->adminUser->widgetOrder && in_array($widgetId, $this->adminUser->widgetOrder)) {
            $weight = array_search($widgetId, $this->adminUser->widgetOrder);
        }
        return (int) $weight;
    }
    public function showWrapper()
    {
        return (bool) $this->wrapper;
    }
    public function isCachable()
    {
        return (bool) $this->cache;
    }
    public function isCachedPerUser()
    {
        return (bool) $this->cachePerUser;
    }
    public function getCacheExpiry()
    {
        return (int) $this->cacheExpiry;
    }
    public function getRequiredPermission()
    {
        return $this->requiredPermission;
    }
    public abstract function getData();
    public abstract function generateOutput($data);
    protected function fetchData($forceRefresh = false)
    {
        $storage = new \App\Helpers\TransientData();
        $storageName = "widget." . $this->getId();
        if ($this->isCachedPerUser()) {
            $authadmin = Auth::guard('admin')->user();
            $adminid = $authadmin ? $authadmin->id : 0;
            $storageName .= ":" . $adminid;
        }
        if ($this->isCachable() && !$forceRefresh) {
            $data = $storage->retrieve($storageName);
            if (!is_null($data)) {
                $decoded = json_decode($data, true);
                if (is_array($decoded) && count($decoded)) {
                    return $decoded;
                }
            }
        }
        $data = $this->getData();
        $data = $this->sanitizeData($data);
        if ($this->isCachable()) {
            $storage->store($storageName, json_encode($data), $this->getCacheExpiry());
        }
        return $data;
    }
    public function sanitizeData($data)
    {
        if ($this instanceof \Modules\Widgets\Activity\Http\Controllers\ActivityController && !empty($data["activity"]["entry"]) && is_array($data["activity"]["entry"])) {
            foreach ($data["activity"]["entry"] as $key => $entry) {
                if (isset($entry["description"])) {
                    $data["activity"]["entry"][$key]["description"] = \App\Helpers\Sanitize::makeSafeForOutput($data["activity"]["entry"][$key]["description"]);
                }
            }
        }
        return $data;
    }
    public function render($forceRefresh = false)
    {
        $data = $this->fetchData($forceRefresh);
        $response = $this->generateOutput($data);
        if (is_array($response)) {
            return json_encode($response);
        }
        return $response;
    }
    public function isDraggable()
    {
        return (bool) $this->draggable;
    }
}
