<?php

namespace App\Helpers\Domains\DomainLookup\Provider;

use DB;

class WhmcsWhois extends BasicWhois
{
    public function getSettings()
    {
        static $tlds = NULL;
        if (is_null($tlds)) {
            $tlds = DB::table("tbldomainpricing")->orderBy("order", "ASC")->pluck("extension", "extension");
            $tlds = $tlds->toArray();
        }
        return array("suggestTlds" => array("FriendlyName" => \Lang::get("admin.generalsuggesttldsinfo"), "Type" => "dropdown", "Description" => "<div class=\"text-muted text-center small\">" . \Lang::get("admin.ctrlclickmultiselection") . "</div>", "Default" => "", "Size" => 10, "Options" => $tlds, "Multiple" => true));
    }
}

?>
