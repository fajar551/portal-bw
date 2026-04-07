<?php

namespace Modules\Addons\Cbmsthememanager\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Zip;

class CbmsthememanagerController extends Controller
{
    public function config()
    {
        return [
            'name' => 'CBMS Theme Manager',
            'description' => 'Module ini digunakan untuk upload file zip theme, delete module dan enable/disable module.',
            'author' => 'CBMS Developer',
            'language' => 'english',
            'version' => '1.0',
            'fields' => [],
        ];
    }

    public function output($vars)
    {
        $themes = \App\Helpers\ThemeManager::all();
        
        return view('cbmsthememanager::index', [
            'themes' => $themes,
        ]);
    }

    public function download(Request $request)
    {
        try {
            $name = $request->input('name');
            $theme = \ThemesManager::get($name);
            if (!$theme) {
                throw new \Exception("Theme not found");
            }

            // prepare zip
            $path = $theme->getPath();
            $themeName = Str::slug(str_replace('/', '-', $name), '-');
            $filename = "{$themeName}.zip";
            $storagepath = Storage::disk('moduledir')->path($filename);
            $zip = Zip::create($storagepath);
            $files = File::files($path, true);
            $f = [];
            foreach ($files as $key => $value) {
                $dir = File::dirname($value);
                $file = File::basename($value);
                $f[] = $dir;
            }
            $result = "success";
            $message = "success";
            try {
                $zip->add($f);
                $zip->close();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result = $message == "No error" ? "success" : "error";
            }

            return response()->json([
                'result' => $result,
                'message' => $message,
                'download_url' => url('cbmsthememanager/download').'?filename='.$filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function downloadTheme(Request $request)
    {
        $filename = $request->input('filename');
        $storagepath = Storage::disk('moduledir')->path($filename);
        // Storage::disk('moduledir')->delete($filename);
        return response()->download($storagepath)->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $validatedData = $request->validate([
            'type' => ['required', Rule::in([config('themes-manager.composer.vendor'), 'orderform'])],
            'status' => ['required'],
            'file' => ['required', 'file', 'mimes:zip'],
        ]);

        // var
        $type = $request->input('type');
        $status = $request->input('status');

        if ($request->hasFile('file')) {
            try {
                // upload zip
                $attachment = $request->file('file');
                $fileNameToSave = $attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
                $upload = Storage::disk('moduledir')->put($filepath, file_get_contents($attachment), 'public');

                // get path
                $path = Storage::disk('moduledir')->path($filepath);

                // get theme name
                $themeName = File::name($path);

                // extract zip
                $zip = Zip::open($path);
                $zip->extract(base_path(config('themes-manager.directory')."/$type/$themeName"));

                // set status
                $vendor_theme = "$type/$themeName";
                $theme = \ThemesManager::get($vendor_theme);
                if (!$theme) {
                    Storage::disk("moduledir")->delete($filepath);
                    throw new \Exception("Theme not found");
                }
                if ($status == "active") {
                    $theme->activate();
                } else {
                    $theme->deactivate();
                }

                // remove zip
                Storage::disk("moduledir")->delete($filepath);
                
                return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "Theme $vendor_theme uploaded & extracted"]);
            } catch (\Exception $e) {
                return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => "No uploaded file"]);
    }

    public function delete(Request $request)
    {
        try {
            $name = $request->input('name');
            $theme = \ThemesManager::get($name);
            if (!$theme) {
                throw new \Exception("Theme not found");
            }

            File::deleteDirectory($theme->getPath());

            return response()->json([
                'result' => 'success',
                'message' => "Theme delete success",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sync(Request $request)
    {
        try {
            $name = $request->input('name');
            $status = $request->input('status');
            $theme = \ThemesManager::get($name);
            if (!$theme) {
                throw new \Exception("Theme not found");
            }

            if ($status) {
                $theme->activate();
            } else {
                $theme->deactivate();
            }

            return response()->json([
                'result' => 'success',
                'message' => "Theme sync success",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'type' => ['required', Rule::in([config('themes-manager.composer.vendor'), 'orderform'])],
            'name' => ['required', 'alpha_num'],
            'parent' => ['required_if:type,orderform'],
            'description' => ['nullable', 'string', 'max:50'],
        ]);

        // var
        $type = $request->input('type');
        $name = strtolower($request->input('name'));
        $description = $request->input('description');
        $parent = $request->input('parent');

        Artisan::call("template:make", [
            'name' => $name,
            '--type' => $type,
            '--parent' => $parent ? $parent : '',
            '--description' => $description ? $description : ''
        ]);
        $output = Artisan::output();
        return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "<pre>$output</pre>"]);
    }
}
