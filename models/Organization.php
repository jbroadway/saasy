<?php

namespace saasy;

/**
 * Contains the logic around managing organizations.
 *
 * Fields:
 *
 * - id - Auto-incrementing ID for each organzation row
 * - name - Organization name
 * - subdomain - Subdomain to link to this organization
 * - level - Account level of the organization
 *
 * Subdomains must be unique, and cannot be 'www'.
 *
 * Level may be used to enable/disable features for an organization
 * through the 'limits' setting. Note that level=0 implies a disabled
 * account.
 */
class Organization extends \Model {
	public $table = '#prefix#saasy_org';

	/**
	 * Model relations.
	 * - $org->accounts() will return the associated Accounts
	 */
	public $fields = array (
		'accounts' => array ('has_many' => '\saasy\Account', 'field_name' => 'org')
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
				a.org = ?
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
		$org = App::org ();
		if (! $org) {
			return App::name ();
		}

		$logo = $org->logo ();

		if ($logo !== false) {
			return sprintf (
				'<img src="%s" title="%s" />',
				$logo,
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