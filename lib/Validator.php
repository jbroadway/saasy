<?php

namespace saasy;

class Validator {
	/**
	 * Make sure email is unique except for current user.
	 */
	public static function email ($email) {
		$res = \DB::shift (
			'select count() from #prefix#user where id != ? and email = ?',
			\User::val ('id'),
			$email
		);
		if ($res > 0) {
			return false;
		}
		return true;
	}

	/**
	 * Make sure subdomain is unique except for current org.
	 */
	public static function subdomain ($subdomain) {
		$org = App::org ();
		$res = \DB::shift (
			'select count() from #prefix#saasy_org where id != ? and subdomain = ?',
			$org->id,
			$subdomain
		);
		if ($res > 0) {
			return false;
		}
		return true;
	}
}

?>