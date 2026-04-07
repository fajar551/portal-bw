<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use DB;
use Carbon\Carbon;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TransientData
{
	protected $chunkSize = 62000;
    const DB_TABLE = "tbltransientdata";
    public static function getInstance()
    {
        return new self();
    }
    public function store($name, $data, $life = 300)
    {
        if (!is_string($data)) {
            return false;
        }
        $expires = time() + (int) $life;
        if ($this->ifNameExists($name)) {
            $this->sqlUpdate($name, $data, $expires);
        } else {
            $this->sqlInsert($name, $data, $expires);
        }
        return true;
    }
    public function chunkedStore($name, $data, $life = 300)
    {
        if (!is_string($data)) {
            return false;
        }
        $expires = time() + (int) $life;
        $this->clearChunkedStorage($name);
        for ($i = 0; 0 < strlen($data); $i++) {
            $this->sqlInsert($name . ".chunk-" . $i, substr($data, 0, $this->chunkSize), $expires);
            $data = substr($data, $this->chunkSize);
        }
        return $this;
    }
    protected function clearChunkedStorage($name)
    {
        DB::table("tbltransientdata")->where("name", "LIKE", $name . ".chunk-%")->delete();
    }
    public function retrieve($name)
    {
        return $this->sqlSelect($name, true);
    }
    public function retrieveChunkedItem($name)
    {
        $data = DB::table("tbltransientdata")->where("name", "LIKE", $name . ".chunk-%")->where("expires", ">=", time())->pluck("data");
        if (0 < count($data)) {
            return implode($data);
        }
        return null;
    }
    public function retrieveByData($data)
    {
        return $this->sqlSelectByData($data, true);
    }
    public function ifNameExists($name)
    {
        $data = $this->sqlSelect($name);
        return $data === null ? false : true;
    }
    public function delete($name)
    {
        $this->sqlDelete($name);
        return true;
    }
    public function purgeExpired($delaySeconds = 120)
    {
        $now = time() - (int) $delaySeconds;
        return DB::table("tbltransientdata")->where("expires", "<", $now)->delete();
    }
    protected function sqlSelect($name, $exclude_expired = false)
    {
        $lookup = DB::table(self::DB_TABLE)->where("name", $name);
        if ($exclude_expired) {
            $lookup->where("expires", ">", time());
        }
        return $lookup->value("data");
    }
    protected function sqlSelectByData($data, $exclude_expired = false)
    {
        if ($exclude_expired) {
            $name = DB::table("tbltransientdata")->where("data", "=", $data)->value("name");
        } else {
            $name = DB::table("tbltransientdata")->where("data", "=", $data)->where("expires", ">", Carbon::now()->toDateString())->value("name");
        }
        return $name;
    }
    protected function sqlInsert($name, $data, $expires)
    {
        return DB::table(self::DB_TABLE)->insertGetId(array("name" => $name, "data" => $data, "expires" => $expires));
    }
    protected function sqlUpdate($name, $data, $expires)
    {
        return DB::table(self::DB_TABLE)->where("name", $name)->update(array("data" => $data, "expires" => $expires));
    }
    public function sqlDelete($name)
    {
        return DB::table(self::DB_TABLE)->where("name", $name)->delete();
    }
}
