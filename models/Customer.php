<?php

namespace saasy;

/**
 * Contains the logic around managing customers.
 *
 * Fields:
 *
 * - id - Auto-incrementing ID for each customer row
 * - name - Customer name
 * - subdomain - Subdomain to link to this customer
 * - level - Account level of the customer
 *
 * Subdomains must be unique, and cannot be 'www'.
 *
 * Level may be used to enable/disable features for a customer
 * through the 'limits' setting. Note that level=0 implies a disabled
 * account.
 *
 * @property mixed id
 * @property string name
 * @property mixed subdomain
 * @property int level
 */
class Customer extends \Model {
	public $table = '#prefix#saasy_customer';

	/**
	 * Model relations.
	 * - $customer->accounts() will return the associated Accounts
	 */
	public $fields = array (
		'accounts' => array ('has_many' => '\saasy\Account', 'field_name' => 'customer')
	);

	/**
	 * List all members (merges account and user info).
	 */
	public function members () {
		$res = \DB::fetch (
			'select a.*, u.name, u.email
			from #prefix#saasy_acct a, #prefix#user u
			where
				a.user = u.id
			and
				a.customer = ?
			order by
				u.name asc',
			$this->id
		);
		foreach ($res as $k => $row) {
			$acct = new Account;
			$acct->id = $row->id;
			$res[$k]->photo = $acct->photo (100, 100);
		}
		return $res;
	}

	/**
	 * The output for the header of the site.
	 */
	public static function header () {
		$company = App::customer ();
		if (! $company) {
			return App::name ();
		}

		$logo = $company->logo ();

		if ($logo !== false) {
			return sprintf (
				'<img src="%s" title="%s" />',
				$logo,
				$company->name
			);
		}
		return $company->name;
	}

	/**
	 * Get the full domain for the current customer.
	 */
	public function domain () {
		$parts = explode ('.', $_SERVER['HTTP_HOST']);
		if (count ($parts === 3)) {
			array_shift ($parts);
		}
		return $this->subdomain . '.' . join ('.', $parts);
	}

	/**
	 * Returns a correctly sized logo if available. If not, returns false.
	 */
	public function logo ($width = 250, $height = 40) {
		$files = glob ('cache/saasy/logos/' . $this->id . '.{jpg,png,gif}', GLOB_BRACE);
		if (count ($files) > 0) {
			$logo = array_shift ($files);
			$ext = strtolower (pathinfo ($logo, PATHINFO_EXTENSION));
			return '/' . \Image::resize ($logo, $width, $height, 'cover', $ext);
		}
		return false;
	}

	/**
	 * Save a new logo image.
	 */
	public function save_logo ($upload) {
		$ext = strtolower (pathinfo ($upload['name'], PATHINFO_EXTENSION));

		if (! is_dir ('cache/saasy')) {
			mkdir ('cache/saasy');
			chmod ('cache/saasy', 0777);
		}

		if (! is_dir ('cache/saasy/logos')) {
			mkdir ('cache/saasy/logos');
			chmod ('cache/saasy/logos', 0777);
		}

		if (file_exists ('cache/saasy/logos/' . $this->id . '.jpg')) {
			unlink ('cache/saasy/logos/' . $this->id . '.jpg');
		}
		if (file_exists ('cache/saasy/logos/' . $this->id . '.png')) {
			unlink ('cache/saasy/logos/' . $this->id . '.png');
		}
		if (file_exists ('cache/saasy/logos/' . $this->id . '.gif')) {
			unlink ('cache/saasy/logos/' . $this->id . '.gif');
		}

		if (! move_uploaded_file (
			$upload['tmp_name'],
			'cache/saasy/logos/' . $this->id . '.' . $ext
		)) {
			return false;
		}
		chmod ('cache/saasy/logos/' . $this->id . '.' . $ext, 0666);
		return true;
	}

	/**
	 * Remove the account logo.
	 */
	public function remove_logo () {
		if (file_exists ('cache/saasy/logos/' . $this->id . '.jpg')) {
			unlink ('cache/saasy/logos/' . $this->id . '.jpg');
		}
		if (file_exists ('cache/saasy/logos/' . $this->id . '.png')) {
			unlink ('cache/saasy/logos/' . $this->id . '.png');
		}
		if (file_exists ('cache/saasy/logos/' . $this->id . '.gif')) {
			unlink ('cache/saasy/logos/' . $this->id . '.gif');
		}
	}
}

?>