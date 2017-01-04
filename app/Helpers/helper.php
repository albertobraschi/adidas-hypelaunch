<?php

namespace App\Http\Helpers;

class Helper
{

	function generateSessionKey()
	{
		$string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$string_shuffled = str_shuffle($string);
		$sessionKey = substr($string_shuffled, 1, 16);
		return $sessionKey;
	}

}