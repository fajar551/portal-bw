<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Emailtemplate extends AbstractModel
{
	protected $table = 'emailtemplates';
	protected $guarded = array("id");
    protected $booleans = array("custom", "disabled", "plaintext");
    protected $commaSeparated = array("attachments", "copyTo", "blindCopyTo");
    public $unique = array();
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public function __toString()
    {
        return $this->name;
    }
	public function scopeMaster($query)
    {
        return $query->where("language", "=", "");
    }
    public static function getActiveLanguages()
    {
        return array_unique(self::where("language", "!=", "")->orderBy("language")->pluck("language")->all());
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function ($template) {
            $existingLanguages = self::where("name", "=", $template->name)->pluck("language")->all();
            if (is_null($existingLanguages)) {
                return true;
            }
            if (!in_array($template->language, $existingLanguages)) {
                return true;
            }
            throw new \App\Exceptions\Model\UniqueConstraint("Email template not unique.");
        });
    }
}
