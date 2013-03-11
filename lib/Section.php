<?php

namespace saasy;

/**
 * Utilities for displaying sections of an app.
 */
class Section {
	/**
	 * The current section name.
	 */
	public static $section = null;

	/**
	 * Set the current section.
	 */
	public static function set ($section) {
		self::$section = $section;
	}

	/**
	 * Get the current section.
	 */
	public static function get () {
		return self::$section;
	}

	/**
	 * Get the display name of the current section.
	 */
	public static function name () {
		$conf = App::conf ();
		if (! is_array ($conf['Sections'])) {
			return '';
		}
		
		if (! isset ($conf['Sections'][self::$section])) {
			return '';
		}
		
		return array_shift ($conf['Sections'][self::$section]);
	}

	/**
	 * Get the body contents of the current section.
	 */
	public static function body () {
		$conf = App::conf ();
		if (! is_array ($conf['Sections'])) {
			return '';
		}
		
		if (! isset ($conf['Sections'][self::$section])) {
			return '';
		}
		
		$keys = array_keys ($conf['Sections'][self::$section]);
		$handler = array_shift ($keys);

		return App::$controller->run ($handler);
	}
}

?>