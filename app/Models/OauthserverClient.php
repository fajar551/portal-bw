<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class OauthserverClient extends Model
{
	use Filterable;
	protected $table = 'oauthserver_clients';
	protected $characterSeparated = array(" " => array("grantTypes", "redirectUri"));

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public static function boot()
	{
		parent::boot();
		static::deleting(function ($model) {
			$model->scopes()->detach();
			$model->rsaKeyPair()->delete();
		});
	}

	public function getFormattedScopes()
	{
		$scopes = $this->scopes()->get();
		$spaceDelimitedScopes = "";
		foreach ($scopes as $scope) {
				$spaceDelimitedScopes .= " " . $scope->scope;
		}
		return trim($spaceDelimitedScopes);
	}
	public function getScopeAttribute()
	{
		return $this->getFormattedScopes();
	}

	public function scopes()
	{
		return $this->belongsToMany(OauthserverScope::class, OauthserverClientScope::class, "client_id", "scope_id");
	}

	public function rsaKeyPair()
	{
		return $this->belongsTo(Rsakeypair::class);
	}
}
