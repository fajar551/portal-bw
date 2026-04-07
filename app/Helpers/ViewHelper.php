<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class ViewHelper
{
    /**
     * generateCssFriendlyClassName
     */
	public static function generateCssFriendlyClassName($value)
    {
        return preg_replace("/[^a-z0-9_-]/", "-", strtolower(trim(strip_tags($value))));
    }
    
    /**
     * render
     * 
     * Compile a string using Laravel Blade
     */
    public static function render($template, $data = []): string
    {
        // HOTFIX: gabisa pake single {, karena akan bentrok dengan internal css (Contoh: div{width:100%;})
        // \Blade::setContentTags('{', '}');
        // $template = \Blade::compileString($template);

        $template = self::transformBladeTags($template, $data);

        $filename = uniqid('blade_');
        $path = config('view.tmp_directory');
        View::addLocation($path);
        $filepath = $path . DIRECTORY_SEPARATOR . "$filename.blade.php";
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents($filepath, trim($template));
        $rendered = View($filename, $data)->render();
        unlink($filepath);
        
        return $rendered;
    }

    public static function renderSmarty($template, $data = []): string
    {
        $filename = uniqid('smarty_');
        $path = config('view.tmp_directory');
        View::addLocation($path);
        $filepath = $path . DIRECTORY_SEPARATOR . "$filename.tpl";
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents($filepath, trim($template));
        $rendered = View($filename, $data)->render();
        unlink($filepath);
        
        return $rendered;
    }

    public static function getDomainGroupLabel($group)
    {
        switch (strtolower($group)) {
            case "hot":
                $groupInfo = "<span class=\"badge badge-danger\" data-group=\"hot\">HOT</span>";
                break;
            case "new":
                $groupInfo = "<span class=\"badge badge-success\" data-group=\"new\">NEW</span>";
                break;
            case "sale":
                $groupInfo = "<span class=\"badge badge-warning\" data-group=\"sale\">SALE</span>";
                break;
            default:
                $groupInfo = "";
        }
        return $groupInfo;
    }
    
    public static function transformBladeTags($template, $data)
    {
        // First: replace single curly bracket to double curly bracket 
        $patterns[] = '/\{+\s*\$(.*?)\s*\}+/is';
        $replacements[] = "{{\$\$1??'-'}}";

        // Second: replace all data contain raw html with double bang curly bracket
        foreach ($data as $key => $value) {
            if (self::isHTML($value)) {
                $patterns[] = '/\{+\s*\$(' .$key .')\?\?\'-\'\s*\}+/is';
                $replacements[] = "{!!$\$1??'-'!!}";
            }
        }

        $template = preg_replace($patterns, $replacements, $template);

        return $template;
    }

    public static function isHTML($string){
        if (is_array($string)) {
            return false;
        }

        return $string != strip_tags($string) ? true : false;
    }

    public static function generateCssFriendlyId($name, $title = "")
    {
        return preg_replace("/[^A-Za-z0-9_-]/", "_", $name . ($title != "" ? "-" . $title : ""));
    }

    public static function alert($text, $alertType = "info", $additionalClasses = "")
    {
        if (!in_array($alertType, array("success", "info", "warning", "danger"))) {
            $alertType = "info";
        }
        switch ($alertType) {
            case "success":
                $icon = "<i class=\"fas fa-check-circle fa-3x pull-left\"></i>";
                break;
            case "warning":
                $icon = "<i class=\"fas fa-exclamation-circle fa-3x pull-left\"></i>";
                break;
            case "danger":
                $icon = "<i class=\"fas fa-times-circle fa-3x pull-left\"></i>";
                break;
            default:
                $icon = "<i class=\"fas fa-info-circle fa-3x pull-left\"></i>";
        }
        $alert = "<div class=\"alert alert-" . $alertType . " clearfix";
        if ($additionalClasses) {
            $alert .= " " . $additionalClasses;
        }
        $alert .= "\" role=\"alert\">" . $icon . "<div class=\"alert-text\">" . $text . "</div></div>";
        return $alert;
    }
}
