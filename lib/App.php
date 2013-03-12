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
	 * The current organization.
	 */
	public static $org = null;

	/**
	 * The current user account.
	 */
	public static $acct = null;

	/**
	 * Returns the app configuration info.
	 */
	public static function conf () {
		if (self::$conf === null) {
			self::$conf = parse_ini_file ('apps/saasy/conf/config.php', true);
			if (file_exists ('conf/app.saasy.' . ELEFANT_ENV . '.php')) {
				$conf2 = parse_ini_file ('conf/app.saasy.' . ELEFANT_ENV . '.php', true);
				self::$conf = array_replace_recursive (self::$conf, $conf2);
			}
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
		$alias = $conf['App Settings']['app_alias'];

		// Rewrite /app_alias/ to /saasy/
		if ($_SERVER['REQUEST_URI'] === '/' . $alias) {
			$_SERVER['REQUEST_URI'] = '/saasy';
		} elseif (strpos ($_SERVER['REQUEST_URI'], '/' . $alias . '/') === 0) {
			$_SERVER['REQUEST_URI'] = str_replace ('/' . $alias . '/', '/saasy/', $_SERVER['REQUEST_URI']);
		}

		// Add bootstrap.js
		$page = $controller->page ();
		$page->add_script ('/apps/saasy/bootstrap/js/bootstrap.min.js');
		$page->add_script ('<script>$(function(){$("input[type=submit]").addClass("btn");});</script>');	

		// Get the org from the subdomain
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts) === 3) {
			$sub = array_shift ($parts);
			$org = Organization::query ()
				->where ('subdomain', $sub)
				->single ();

			if ($org && ! $org->error) {
				self::org ($org);

				// Get the account from the user
				if (\User::require_login ()) {
					$acct = Account::query ()
						->where ('user', \User::val ('id'))
						->where ('org', $org->id)
						->single ();

					if ($acct && ! $acct->error) {
						self::acct ($acct);
					}
				}
			}
		}
	}

	/**
	 * Get the domain minus any subdomain.
	 */
	public static function base_domain () {
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts) === 3) {
			array_shift ($parts);
		}
		return join ('.', $parts);
	}

	/**
	 * Get/set the current organization.
	 */
	public static function org ($org = null) {
		if ($org !== null) {
			self::$org = $org;
		}
		return self::$org;
	}

	/**
	 * Get/set the current user account.
	 */
	public static function acct ($acct = null) {
		if ($acct !== null) {
			self::$acct = $acct;
		}
		return self::$acct;
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
		return '/' . $conf['App Settings']['app_alias'];
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
	 * Whether the app has search capabilities.
	 */
	public static function has_search () {
		if (! \User::require_login ()) {
			return false;
		}

		$conf = self::$conf;
		return ($conf['App Settings']['search']) ? true : false;
	}

	/**
	 * Add search to your app.
	 */
	public static function search () {
		$conf = self::$conf;
		if ($conf['App Settings']['search']) {
			return self::$controller->run ($conf['App Settings']['search']);
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
		if (! \User::require_login ()) {
			return '';
		}

		$conf = self::$conf;
		if (! is_array ($conf['Sections'])) {
			$conf['Sections'] = array ();
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

		// Add account
		$class = (strpos ($_SERVER['REQUEST_URI'], '/saasy/account') === 0)
			? ' class="active"'
			: '';
		$out .= sprintf (
			'<li%s><a href="%s/%s">%s</a></li>',
			$class,
			self::href (),
			'account',
			__ ('Account')
		);

		// Add sign out
		$out .= sprintf (
			'<li><a href="/user/logout">%s</a></li>',
			__ ('Sign Out')
		);

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