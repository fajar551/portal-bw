<?php

namespace Modules\Addons\Cbmshookcallbackmanager\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Zip;

class CbmshookcallbackmanagerController extends Controller
{
    public function config()
    {
        return [
            'name' => 'CBMS Hooks & Callback Manager',
            'description' => 'Module ini digunakan untuk upload file hooks dan callback gateway',
            'author' => 'CBMS Developer',
            'language' => 'english',
            'version' => '1.0',
            'fields' => [],
        ];
    }

    public function output($vars)
    {
        // get events
        $base_path = base_path("app/Events");
        $files = File::files($base_path);
        $events = [];
        foreach ($files as $key => $value) {
            $dir = File::dirname($value);
            $file = File::basename($value);
            $events[] = str_replace(".php", "", $file);
        }

        // hooks list
        $hooks = [];
        $files = File::files(base_path("app/Hooks"));
        foreach ($files as $key => $value) {
            $dir = File::dirname($value);
            $file = File::basename($value);
            if (!in_array($file, ['Example.php'])) {
                $hooks[] = [
                    'type' => 'Hook',
                    'file' => $file,
                    'name' => str_replace(".php", "", $file),
                    'path' => $dir,
                    'full_path' => $value->getPathname(),
                ];
            }
        }
        // dd($hooks);

        // callbak list
        $callbacks = [];
        $files = File::files(base_path("app/Http/Controllers/Callback"));
        foreach ($files as $key => $value) {
            $dir = File::dirname($value);
            $file = File::basename($value);
            $callbacks[] = [
                'type' => 'Callback',
                'file' => $file,
                'name' => str_replace(".php", "", $file),
                'path' => $dir,
                'full_path' => $value->getPathname(),
            ];
        }

        $scripts = collect(array_merge($hooks, $callbacks));
        
        return view('cbmshookcallbackmanager::index', compact('events', 'scripts'));
    }

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'command' => ['required'],
            'event' => ['nullable', 'string'],
            'name' => ['required', 'alpha_num'],
        ]);

        // var
        $command = $request->input('command');
        $event = $request->input('event');
        $name = $request->input('name');

        
        try {
            switch ($command) {
                case 'hook:make':
                    Artisan::call("$command", [
                        'name' => $name,
                        '--event' => $event,
                    ]);
                    $output = Artisan::output();
                break;
                case 'callback:make':
                    Artisan::call("$command $name");
                    $output = Artisan::output();
                break;
                
                default:
                    throw new \Exception("Command not found");
                break;
            }
            return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "<pre>$output</pre>"]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => $e->getMessage()]);
        }
    }

    public function upload(Request $request)
    {
        $validatedData = $request->validate([
            'type' => ['required'],
            'file' => ['required', 'file', 'mimes:zip'],
        ]);

        if ($request->hasFile('file')) {
            try {
                // vars
                $type = $request->input('type');
                
                switch ($type) {
                    case 'hook':
                        $destinationPath = base_path("app/Hooks");
                    break;
                    case 'callback':
                        $destinationPath = base_path("app/Http/Controllers/Callback");
                    break;
                    
                    default:
                        throw new \Exception("Type not found");
                    break;
                }

                // upload zip
                $attachment = $request->file('file');
                $fileNameToSave = $attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
                $upload = Storage::disk('moduledir')->put($filepath, file_get_contents($attachment), 'public');
                
                // get path
                $path = Storage::disk('moduledir')->path($filepath);

                // extract zip
                $zip = Zip::open($path);
                $zip->extract($destinationPath);

                // remove zip
                Storage::disk("moduledir")->delete($filepath);

                return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => ucfirst($type)." $filename uploaded & extracted"]);
            } catch (\Exception $e) {
                return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => $e->getMessage()]);
            }
        }
    }

    public function download(Request $request)
    {
        $file = $request->input('file');

        return response()->download($file);
    }

    public function remove(Request $request)
    {
        $file = $request->input('file');
        File::delete($file);

        return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "$file deleted"]);
    }
}
