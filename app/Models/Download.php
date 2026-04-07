<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Download extends AbstractModel
{
	protected $table = 'downloads';
	protected $columnMap = array("downloadCategoryId" => "category", "timesDownloaded" => "downloads", "fileLocation" => "location", "clientDownloadOnly" => "clientsonly", "isHidden" => "hidden", "isProductDownload" => "productdownload");
	protected $booleans = array("clientDownloadOnly", "isHidden", "isProductDownload");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public function asLink()
    {
        return \App\Helpers\Cfg::getValue("SystemURL") . "/dl.php?type=d&amp;id=" . $this->id;
    }
    public function downloadCategory()
    {
        return $this->belongsTo(Downloadcat::class, "category");
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, "tblproduct_downloads");
    }
    public function scopeConsiderProductDownloads($query)
    {
        if (!\App\Helpers\Cfg::getValue("DownloadsIncludeProductLinked")) {
            $query = $query->where("productDownload", false);
        }
        return $query;
    }
    public function scopeTopDownloads($query, $count = 5)
    {
        $query = $this->scopeConsiderProductDownloads($query);
        return $query->whereHas("downloadCategory", function ($subQuery) {
            $subQuery->where("hidden", false);
        })->where("hidden", false)->orderBy("downloads", "desc")->limit($count);
    }
	// public function translatedNames()
    // {
    //     return $this->hasMany("WHMCS\\Language\\DynamicTranslation", "related_id")->where("related_type", "=", "product.{id}.name")->select(array("language", "translation"));
    // }
    // public function translatedDescriptions()
    // {
    //     return $this->hasMany("WHMCS\\Language\\DynamicTranslation", "related_id")->where("related_type", "=", "product.{id}.description")->select(array("language", "translation"));
    // }
    public function scopeInCategory($query, $catId)
    {
        return $query->where("category", "=", $catId);
    }
    public function scopeVisible($query)
    {
        return $query->where("hidden", "=", "0");
    }
    public function scopeCategoryVisible($query)
    {
        return $query->whereIn("category", Downloadcat::visible()->pluck("id")->toArray());
    }
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use($search) {
            $searchPattern = "%" . $search . "%";
            return $query->orWhere("title", "like", $searchPattern)->orWhere("description", "like", $searchPattern);
        });
    }
}
