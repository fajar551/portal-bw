<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ThemeManager
{
    public static function orderformThemeVendor()
    {
        return "orderform";
    }
    public static function orderformTheme()
    {
        return self::orderformThemeVendor();
    }
    public static function orderformThemeDefault()
    {
       $themeVal = Cfg::getValue('OrderFormTemplate');
      //   return 'standard-cart2';
        return $themeVal;
    }
    public static function defaultThemes()
    {
        return ['admin', 'one', 'standard-cart2'];
    }
	public static function all()
	{
		$themes = \ThemesManager::all();

        $templates = [];
        foreach ($themes as $theme) {
            $extends = [];
            if ($theme->getParent()) {
                $parent = $theme->getParent();
                $extends = [
                    'name' => $parent->getName(),
                    'vendor' => $parent->getVendor(),
                ];
            }
            $templates[] = [
                'name' => $theme->getName(),
                'fullname' => $theme->getVendor()."/".$theme->getName(),
				'path' => $theme->getPath(),
				'asset_path' => $theme->getAssetsPath(),
                'thumbnail_url' => asset($theme->getAssetsPath()."img/thumbnail.png"),
				'view_path' => $theme->getViewPaths(),
                'vendor' => $theme->getVendor(),
                'version' => $theme->get('version'),
                'description' => $theme->get('description'),
                'extends' => $extends,
                'default' => $theme->getName() === config('themes-manager.fallback_theme') ? 'X' : '',
                'active' => $theme->isActive(),
                'status' => $theme->isActive() ? 'Active' : 'Disabled',
				'layouts' => $theme->listLayouts(),
            ];
        }

		return $templates;
	}
}
