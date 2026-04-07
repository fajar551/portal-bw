<?php

namespace App\Console\Commands;

use Database;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CepsModelConverter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:convert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch convert databases table to model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tables = $this->tablesDB();
        
        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        $prefix = Database::prefix();

        foreach ($tables as $table) {
            if (Str::startsWith($table, $prefix)) {
                $table = Str::replaceFirst($prefix, '', $table);
            }
            $name = Str::singular($table);
            $name = Str::camel($name);
            $name = Str::ucfirst($name);
            $path = app_path() . "/Models/{$name}.php";
            if (!File::exists($path)) {
                $params = [
                    'className' => $name,
                    'tableName' => $table,
                ];
                File::put($path, self::fileSource($params));

                // return $this->info("Model {$name} successfuly converted.");
            }

            $bar->advance();
        }

        $bar->finish();
    }

    private static function fileSource($params = [])
    {
        $file = "<?php\n\n";
        $file .= "namespace App\Models;\n\n";
        $file .= "use Database;\n";
        $file .= "use Illuminate\Database\Eloquent\Model;\n\n";
        $file .= "class {$params['className']} extends Model\n";
        $file .= "{\n";
        $file .= "\t" . 'protected $table = \''. $params['tableName'] .'\';' . "\n\n";
        $file .= "\t" . 'public function __construct(array $attributes = [])' . "\n";
        $file .= "\t" . "{\n";
        $file .= "\t\t" . '$this->table = Database::prefix() . $this->table;' . "\n";
        $file .= "\t\t" . 'parent::__construct($attributes);' . "\n";
        $file .= "\t" . "}\n";
        $file .= "}\n";

        return $file;
    }

    private function tablesDB()
    {
        $dbname = DB::connection()->getDatabaseName();
        $response = [];
        $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$dbname'");
        foreach ($tables as $table) {
            $response[] = $table->TABLE_NAME;
        }

        return $response;
    }
}
