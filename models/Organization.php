<?php

namespace saasy;

class Organization extends \Model {
	public $table = '#prefix#saasy_org';

	public static function header () {
		return 'Org Name';
	}
}

?>