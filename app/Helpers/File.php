<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class File
{
	protected $path = NULL;
    public function __construct($path)
    {
        if (!trim($path)) {
            throw new \Exception("No file path supplied.");
        }
        if (!\App\Helpers\OperatingSystem::isWindows() && realpath(dirname($path)) != dirname($path)) {
            throw new \Exception("File path invalid.");
        }
        if (!$this->isFileNameSafe(basename($path))) {
            throw new \Exception("Filename invalid.");
        }
        $this->path = $path;
    }
    public function exists()
    {
        return file_exists($this->path);
    }
    public function create($contents)
    {
        if (@file_put_contents($this->path, $contents) === false) {
            throw new \App\Exceptions\File\NotCreated($this->path);
        }
        return $this;
    }
    public function delete()
    {
        if (file_exists($this->path)) {
            if (unlink($this->path)) {
                return $this;
            }
            throw new \App\Exceptions\File\NotDeleted($this->path);
        }
        throw new \App\Exceptions\File\NotFound($this->path);
    }
    public function isFileNameSafe($filename)
    {
        if (empty($filename)) {
            return false;
        }
        if (strpos($filename, "") !== false) {
            return false;
        }
        if (strpos($filename, DIRECTORY_SEPARATOR) !== false || strpos($filename, PATH_SEPARATOR) !== false) {
            return false;
        }
        if (strpos($filename, chr(8)) !== false) {
            return false;
        }
        if (substr($filename, 0, 1) === ".") {
            return false;
        }
        $inputValidation = new \App\Helpers\Validation();
        if ($inputValidation->escapeshellcmd($filename) != $filename) {
            return false;
        }
        return true;
    }
    public function contents()
    {
        return file_get_contents($this->path);
    }
    public function getExtension()
    {
        $fileNameParts = explode(".", $this->getFileName());
        return "." . strtolower(end($fileNameParts));
    }
}
