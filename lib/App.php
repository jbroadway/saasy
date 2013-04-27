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
	 * @var \Controller
	 */
	public static $controller = null;

	/**
	 * The current customer.
	 * @var Customer
	 */
	public static $customer = null;

	/**
	 * The current user account.
	 * @var Account
	 */
	public static $acct = null;

	/**
	 * The account limits.
	 */
	public static $limits = null;

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
	 *
	 * @param \Controller $controller
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

		// Get the customer from the subdomain
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts) === 3) {
			$sub = array_shift ($parts);
			/** @var $customer Customer */
			$customer = Customer::query ()
				->where ('subdomain', $sub)
				->single ();

			if ($customer && ! $customer->error) {
				self::customer ($customer);

				// Get the account from the user
				if (\User::require_login ()) {
					/** @var $acct Account */
					$acct = Account::query ()
						->where ('user', \User::val ('id'))
						->where ('customer', $customer->id)
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
	 *
	 * @return string
	 */
	public static function base_domain () {
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts) === 3) {
			array_shift ($parts);
		}
		return join ('.', $parts);
	}

	/**
	 * Get/set the current customer.
	 *
	 * @param Customer $customer
	 *
	 * @return Customer
	 */
	public static function customer ($customer = null) {
		if ($customer !== null) {
			self::$customer = $customer;
		}
		return self::$customer;
	}

	/**
	 * Get/set the current user account.
	 *
	 * @param Account $acct
	 *
	 * @return Account
	 */
	public static function acct ($acct = null) {
		if ($acct !== null) {
			self::$acct = $acct;
		}
		return self::$acct;
	}

	/**
	 * Authorize the user to see the account, or take
	 * appropriate action if they're not authorized.
	 *
	 * @param \Page $page
	 * @param \Template $tpl
	 *
	 * @return bool
	 */
	public static function authorize ($page, $tpl) {
		$conf = self::conf ();
		$www = ($conf['App Settings']['include_www']) ? "www." : "";
		// Send non-customer requests to the main site
		$customer = self::customer ();
		if (! $customer) {
			if (strpos ($_SERVER['REQUEST_URI'], '/saasy/') === 0) {
				self::$controller->redirect ('/');
			}

			$url = ($_SERVER['REQUEST_URI'] === '/')
				? 'admin/page'
				: 'admin/page' . $_SERVER['REQUEST_URI'];
			echo self::$controller->run ($url);
			return false;
		}

		// Require user to be logged in
		if (! \User::is_valid ()) {
			$page->title = __ ('Members');
			echo self::$controller->run ('user/login');
			return false;
		}

		// Does this user belong to the company?
		$acct = self::acct ();
		if (! $acct || $acct->customer !== $customer->id || $acct->enabled == 0) {
			\User::logout ();
			$page->title = __ ('Unauthorized');
			echo $tpl->render ('saasy/unauthorized');
			return false;
		}

		return true;
	}

	/**
	 * Authorize the user to see the account, or return false
	 * to allow the handler to return a REST error response.
	 *
	 * @return bool
	 */
	public static function authorize_restful () {
		$customer = self::customer ();
		if (! $customer) {
			return false;
		}

		// Require user to be logged in
		if (! \User::is_valid ()) {
			return false;
		}

		// Does this user belong to the company?
		$acct = self::acct ();
		if (! $acct || $acct->customer !== $customer->id || $acct->enabled == 0) {
			return false;
		}

		return true;
	}

	/**
	 * Get the app name.
	 *
	 * @return string
	 */
	public static function name () {
		$conf = self::conf ();
		return $conf['App Settings']['app_name'];
	}

	/**
	 * Get the href prefix for the app.
	 *
	 * @return string
	 */
	public static function href () {
		$conf = self::conf ();
		return '/' . $conf['App Settings']['app_alias'];
	}

	/**
	 * Take a handler name and turn it into a URL that will map
	 * correctly to a Saasy-enabled handler by rewriting the
	 * app name portion of the handler to the [App Settings]
	 * app_alias value.
	 *
	 * @return string
	 */
	public static function make_href ($handler) {
		$conf = self::conf ();
		if (strpos ($handler, $conf['App Settings']['app_alias'] . '/') === 0) {
			return '/' . $handler;
		}
		return '/' . preg_replace ('/^[^\/]+\//', $conf['App Settings']['app_alias'] . '/', $handler);
	}

	/**
	 * Fetch the footer menu for your app.
	 *
	 * @return string
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
	 *
	 * @return string
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
	 *
	 * @return bool
	 */
	public static function has_search () {
		if (! \User::require_login ()) {
			return false;
		}

		$conf = self::$conf;
		return ($conf['App Settings']['search']) ? true : false;
	}

	/**
	 * Whether the app's search has autocomplete capabilities.
	 */
	public static function has_search_autocomplete () {
		if (! \User::require_login ()) {
			return false;
		}

		$conf = self::$conf;
		return ($conf['App Settings']['search_autocomplete']) ? true : false;
	}

	/**
	 * Add search to your app.
	 *
	 * @return string
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
	 *
	 * @return string
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
	 * Get the account limits for all or a specific level.
	 * Calls the method in [App Settings][limit] for a list
	 * of limits, which should return an array such as:
	 *
	 *     array (
	 *         1 => array (
	 *             'name' => __ ('Free'),
	 *             'members' => 0 // no sub-accounts
	 *         ),
	 *         2 => array (
	 *             'name' => __ ('Basic'),
	 *             'members' => 10 // 10 sub-accounts
	 *         ),
	 *         3 => array (
	 *             'name' => __ ('Pro'),
	 *             'members' => -1 // unlimited sub-accounts
	 *         )
	 *     );
	 *
	 * Note: Level 0 implies a disabled account.
	 */
	public static function search_autocomplete () {
		$conf = self::$conf;
		if (! $conf['App Settings']['search_autocomplete']) {
			return array ();
		}
		return call_user_func ($conf['App Settings']['search_autocomplete']);
	}

	/**
	 * Generate the top-level menu for the sections of your app.
	 *
	 * @param string|bool $current
	 *
	 * @return string
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
			if (strpos ($key, 'dropdown:') === 0) {
				// handle dropdown menu options
				$key = str_replace ('dropdown:', '', $key);
				$label = array_shift ($value);
				$out .= '<li class="dropdown">'
					. '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'
					. $label . ' <b class="caret"></b></a>'
					. '<ul class="dropdown-menu">';
				foreach ($value as $handler => $label) {
					$out .= sprintf (
						'<li><a href="%s">%s</a></li>',
						self::make_href ($handler),
						$label
					);
				}
				$out .= '</ul></li>';
				continue;
			}

			// handle regular menu options
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
	 *
	 * @return array
	 */
	public static function first_section () {
		$conf = self::$conf;
		if (! is_array ($conf['Sections'])) {
			return '';
		}

		$keys = array_keys ($conf['Sections']);
		return array_shift ($keys);
	}

	/**
	 * Get the account limits for all or a specific level.
	 * Calls the method in [App Settings][limit] for a list
	 * of limits, which should return an array such as:
	 *
	 *     array (
	 *         1 => array (
	 *             'name' => __ ('Free'),
	 *             'members' => 0 // no sub-accounts
	 *         ),
	 *         2 => array (
	 *             'name' => __ ('Basic'),
	 *             'members' => 10 // 10 sub-accounts
	 *         ),
	 *         3 => array (
	 *             'name' => __ ('Pro'),
	 *             'members' => -1 // unlimited sub-accounts
	 *         )
	 *     );
	 *
	 * Note: Level 0 implies a disabled account.
	 *
	 * @param string|null $level
	 *
	 * @return array|mixed|null
	 */
	public static function limits ($level = null) {
		if (self::$limits === null) {
			$conf = self::$conf;
			if ($conf['App Settings']['limits']) {
				self::$limits = call_user_func ($conf['App Settings']['limits']);
			} else {
				self::$limits = array ();
			}
		}

		if ($level !== null) {
			if (isset (self::$limits[$level])) {
				return self::$limits[$level];
			}
			return array ();
		}
		return self::$limits;
	}

	/**
	 * Get a specific account limit value, with a default value
	 * you can set if no limit is found. For example:
	 *
	 *     $customer = saasy\App::customer ();
	 *     $member_limit = saasy\App::limit ($customer->level, 'members', -1);
	 */
	/**
	 * @param string $level
	 * @param string $key
	 * @param integer $default
	 *
	 * @return bool
	 */
	public static function limit ($level, $key, $default = -1) {
		$limits = self::limits ($level);
		return isset ($limits[$key]) ? $limits[$key] : $default;
	}
}

?>