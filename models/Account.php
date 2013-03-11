<?php

namespace saasy;

class Account extends \Model {
	public $table = '#prefix#saasy_acct';

	public static function header () {
		return 'User Name | Sign Out';
	}
}

?>