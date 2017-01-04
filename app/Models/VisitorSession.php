<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorSession extends Model
{
	const SESSION_STATUS_NEW = "new";
	const SESSION_STATUS_WAITING = "waiting";
	const SESSION_STATUS_REDIRECTED = "redirected";
	const SESSION_STATUS_EXPIRED = "expired";

	const SESSION_ACTIVE = 1;
	const SESSION_NOT_ACTIVE = 0;

	const DEFAULT_WAITING_TIME = 20; //second
	const DEFAULT_SESSION_LIFETIME = 60; //second

	use SoftDeletes;

	protected $table = "adidas_hypelaunch_visitor_session";
    public $timestamps = true;
    protected $fillable = ['session_key', 'status', 'active'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function generateSessionKey()
	{
		$string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$string_shuffled = str_shuffle($string);
		$sessionKey = substr($string_shuffled, 1, 16);
		return $sessionKey;
	}	

}
