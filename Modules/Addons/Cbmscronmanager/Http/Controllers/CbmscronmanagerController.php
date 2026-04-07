<?php

namespace Modules\Addons\Cbmscronmanager\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Zip;

class CbmscronmanagerController extends Controller
{
    public function config()
    {
        return [
            'name' => 'CBMS Cron Manager',
            'description' => 'Module ini digunakan untuk manage cron',
            'author' => 'CBMS Developer',
            'language' => 'english',
            'version' => '1.0',
            'fields' => [],
        ];
    }

    public function output($vars)
    {
        // crons list
        $crons = [];
        $files = File::files(base_path("app/Console/Crons"));
        foreach ($files as $key => $value) {
            $dir = File::dirname($value);
            $file = File::basename($value);

            // find signature and description
            $command = "";
            $description = "";
            $lines = file($value->getPathname());
            foreach ($lines as $line) {
                if (strpos($line, 'protected $signature') !== false) {
                    $command = str_replace(["protected", '$signature', "=", "'", ";"], "", $line);
                    $command = trim($command);
                }
                if (strpos($line, 'protected $description') !== false) {
                    $description = str_replace(["protected", '$description', "=", "'", ";"], "", $line);
                    $description = trim($description);
                }
            }

            if (!in_array($file, ['Example.php'])) {
                $crons[] = [
                    'type' => 'Cron',
                    'file' => $file,
                    'name' => str_replace(".php", "", $file),
                    'path' => $dir,
                    'full_path' => $value->getPathname(),
                    'command' => $command,
                    'description' => $description,
                ];
            }
        }
        // dd($crons);

        $scripts = collect($crons);

        return view('cbmscronmanager::index', compact('scripts'));
    }

    public function addNew(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'alpha_num'],
            'signature' => ['required', 'string'],
            'description' => ['nullable'],
        ]);

        // var
        $command = "cron:generate";
        $name = $request->input('name');
        $signature = $request->input('signature');
        $description = $request->input('description');

        try {
            Artisan::call("$command", [
                'name' => $name,
                '--signature' => $signature,
                '--description' => $description,
            ]);
            $output = Artisan::output();
            return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "<pre>$output</pre>"]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['alert-type' => 'danger', 'alert-message' => $e->getMessage()]);
        }
    }

    public function upload(Request $request)
    {
        $validatedData = $request->validate([
            'file' => ['required', 'file', 'mimes:zip'],
        ]);

        if ($request->hasFile('file')) {
            try {
                $destinationPath = base_path("app/Console/Crons");

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

                return redirect()->back()->with(['alert-type' => 'success', 'alert-message' => "$filename uploaded & extracted"]);
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
