<?php

namespace saasy;

/**
 * General helper methods.
 */
class App {
	/**
	 * The app configuration info.
	 */
	public static $conf = null;

	/**
	 * The Controller object.
	 */
	public static $controller = null;

	/**
	 * Returns the app configuration info.
	 */
	public static function conf () {
		if (self::$conf === null) {
			self::$conf = parse_ini_file ('apps/saasy/conf/config.php', true);
		}
		return self::$conf;
	}

	/**
	 * Call this from Elefant's bootstrap.php file so that
	 * links to `/{app_url}/*` map to `/saasy/*`.
	 *
	 * Usage:
	 *
	 *     saasy\App::bootstrap ($controller);
	 */
	public static function bootstrap ($controller) {
		self::$controller = $controller;

		$conf = self::conf ();
		$app_url = $conf['App Settings']['app_url'];

		if ($_SERVER['REQUEST_URI'] === '/' . $app_url) {
			$_SERVER['REQUEST_URI'] = '/saasy';
		} elseif (strpos ($_SERVER['REQUEST_URI'], '/' . $app_url . '/') === 0) {
			$_SERVER['REQUEST_URI'] = str_replace ('/' . $app_url . '/', '/saasy/', $_SERVER['REQUEST_URI']);
		}

		$page = $controller->page ();
		$page->add_script ('/apps/saasy/bootstrap/js/bootstrap.min.js');
	}

	/**
	 * Get the app name.
	 */
	public static function name () {
		$conf = self::conf ();
		return $conf['App Settings']['app_name'];
	}

	/**
	 * Get the href prefix for the app.
	 */
	public static function href () {
		$conf = self::conf ();
		return '/' . $conf['App Settings']['app_url'];
	}

	/**
	 * Fetch the footer menu for your app.
	 */
	public static function footer () {
		$conf = self::conf ();
		if ($conf['App Settings']['footer']) {
			return self::$controller->run ($conf['App Settings']['footer']);
		}
		return '';
	}

	/**
	 * Load the custom theme for your app.
	 */
	public static function theme () {
		$conf = self::conf ();
		if ($conf['App Settings']['theme']) {
			return self::$controller->run ($conf['App Settings']['theme']);
		}
		return '';
	}

	/**
	 * Add search to your app.
	 */
	public static function search () {
		$conf = self::$conf;
		if ($conf['App Settings']['search']) {
			return self::$controller->run (
				$conf['App Settings']['search'],
				array ('header' => false)
			);
		}
		return '';
	}

	/**
	 * Add search to your app.
	 */
	public static function search_header () {
		$conf = self::$conf;
		if ($conf['App Settings']['search']) {
			return self::$controller->run (
				$conf['App Settings']['search'],
				array ('header' => true)
			);
		}
		return '';
	}

	/**
	 * Generate the top-level menu for the sections of your app.
	 */
	public static function menu ($current = false) {
		$conf = self::$conf;
		if (! is_array ($conf['Sections'])) {
			return '';
		}

		if (! $current) {
			$current = Section::get ();
		}

		$out = '<ul class="nav">';
		foreach ($conf['Sections'] as $key => $value) {
			$class = ($current && $current === $key)
				? ' class="active"'
				: '';

			$out .= sprintf (
				'<li%s><a href="%s/%s">%s</a></li>',
				$class,
				self::href (),
				$key,
				array_shift ($value)
			);
		}
		return $out . '</ul>';
	}

	/**
	 * Get the first section.
	 */
	public static function first_section () {
		$conf = self::$conf;
		if (! is_array ($conf['Sections'])) {
			return '';
		}
		
		$keys = array_keys ($conf['Sections']);
		return array_shift ($keys);
	}
}

?>