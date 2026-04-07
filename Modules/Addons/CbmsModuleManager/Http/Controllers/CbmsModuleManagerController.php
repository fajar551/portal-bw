<?php

namespace Modules\Addons\CbmsModuleManager\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Zip;

class CbmsModuleManagerController extends Controller
{
    public function config()
    {
        return [
            'name' => 'CBMS Module Manager',
            'description' => 'Module ini digunakan untuk upload file zip module, delete module dan enable/disable module.',
            'author' => 'CBMS Developer',
            'language' => 'english',
            'version' => '1.0',
            'fields' => [],
        ];
    }

    public function output($vars)
    {
        $moduledirectories = [];
        $base_path = base_path("Modules");
        $paths = File::directories($base_path);
        foreach ($paths as $key => $path) {
           $folder = explode(DIRECTORY_SEPARATOR, $path);
           $folder = end($folder);
           $subpaths = File::directories($base_path . DIRECTORY_SEPARATOR . $folder);
           $subfolders = [];
            foreach ($subpaths as $subpath) {
                $subfolder = explode(DIRECTORY_SEPARATOR, $subpath);
                $subfolder = end($subfolder);
                $subfolders[] = $subfolder;
            }
            $moduledirectories[$folder] = $subfolders;
        }
        
        return view("cbmsmodulemanager::index", compact('moduledirectories'));
    }

    public function download($filename)
    {
        $storagepath = Storage::disk('moduledir')->path($filename);
        // Storage::disk('moduledir')->delete($filename);
        return response()->download($storagepath)->deleteFileAfterSend(true);
    }

    public function downloadModule(Request $request)
    {
        $base_path = base_path("Modules");
        $moduleName = $request->input('module');
        $path = $request->input('path');
        $path = $base_path . DIRECTORY_SEPARATOR . $path;
        $module = \Module::find($moduleName);
        if (!$module) {
            return response()->json([
                'result' => 'error',
                'message' => "Module not found",
            ]);
        }

        $directory = "$path" . DIRECTORY_SEPARATOR . "$moduleName";
        $filename = "{$moduleName}.zip";
        $storagepath = Storage::disk('moduledir')->path($filename);
        $zip = Zip::create($storagepath);
        $files = File::files($directory, true);
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
        
        // Storage::disk('moduledir')->download($filename);
        return response()->json([
            'result' => $result,
            'message' => $message,
            'download_url' => route('cbmsmodulemanager.download', $filename),
        ]);
        // return response()->download($storagepath);
    }

    public function removeModule(Request $request)
    {
        $moduleName = $request->input('module');

        $module = \Module::find($moduleName);
        if (!$module) {
            return response()->json([
                'result' => 'error',
                'message' => "Module not found",
            ]);
        }

        $module->delete();

        return response()->json([
            'result' => 'success',
            'message' => "Module removed",
            // 'data' => $request->all(),
        ]);
    }

    public function syncStatus(Request $request)
    {
        $moduleName = $request->input('module');
        $status = $request->input('status');

        $module = \Module::find($moduleName);
        if (!$module) {
            return response()->json([
                'result' => 'error',
                'message' => "Module not found",
            ]);
        }

        if ($status) {
            $module->enable();
        } else {
            $module->disable();
        }

        return response()->json([
            'result' => 'success',
            'message' => "Module sync success",
            // 'data' => $request->all(),
        ]);
    }

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'command' => ['required'],
            'name' => ['required', 'alpha_num'],
        ]);

        // var
        $command = $request->input('command');
        $name = $request->input('name');

        Artisan::call("$command $name");
        $output = Artisan::output();
        return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "<pre>$output</pre>"]);
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'type' => ['required'],
            'status' => ['required'],
            'file' => ['required', 'file', 'mimes:zip'],
        ]);

        if ($request->hasFile('file')) {
            try {
                // vars
                $type = $request->input('type');
                $status = $request->input('status');

                // upload zip
                $attachment = $request->file('file');
                $fileNameToSave = $attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
                $upload = Storage::disk('moduledir')->put($filepath, file_get_contents($attachment), 'public');
                
                // get path
                $path = Storage::disk('moduledir')->path($filepath);

                // validate zip file
                // $is_valid = Zip::check($path);

                // if (!$is_valid) {
                //     return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => "No valid zip file"]);
                // }

                // get module name
                $moduleName = File::name($path);

                // extract zip
                $zip = Zip::open($path);
                $zip->extract(base_path("Modules/$type/$moduleName"));

                // set status
                $module = \Module::find($moduleName);
                if ($status == "active") {
                    $module->enable();
                } else {
                    $module->disable();
                }

                // remove zip
                Storage::disk("moduledir")->delete($filepath);
                
                return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "Module $moduleName uploaded & extracted"]);
            } catch (\Exception $e) {
                return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => "No uploaded file"]);
    }
}
