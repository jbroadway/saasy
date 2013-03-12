<?php

namespace saasy;

/**
 * Contains the logic around managing organizations.
 */
class Organization extends \Model {
	public $table = '#prefix#saasy_org';

	/**
	 * The output for the header of the site.
	 */
	public static function header () {
		$org = App::org ();
		if (! $org) {
			return App::name ();
		}

		if (! empty ($org->logo)) {
			return sprintf (
				'<img src="%s" title="%s" />',
				$org->logo,
				$org->name
			);
		}
		return $org->name;
	}

	/**
	 * Get the full domain for the current organzation.
	 */
	public function domain () {
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts === 3)) {
			array_shift ($parts);
		}
		return $this->subdomain . '.' . join ('.', $parts);
	}
}

?>