<?php

namespace saasy;

/**
 * Contains the logic around managing accounts.
 *
 * Fields:
 *
 * - id - Auto-incrementing ID for each account row
 * - user - User ID, ref to \User
 * - org - Organization ID, ref to saasy\Organization
 * - type - 'owner' or 'member' (expandable)
 * - enabled - 0 = no, 1 = yes
 *
 * Indexed by user and org. User/org combo must be unique.
 */
class Account extends \Model {
	public $table = '#prefix#saasy_acct';

	/**
	 * Model relations.
	 * - $acct->user() will return the associated User
	 * - $acct->org() will return the associated Organization
	 */
	public $fields = array (
		'user' => array ('has_one' => '\User'),
		'org' => array ('has_one' => '\saasy\Organization')
	);

	/**
	 * Returns a correctly sized profile photo if available.
	 * If not, returns a default profile photo.
	 */
	public function photo ($width = 160, $height = 160) {
		$files = glob ('cache/saasy/accounts/' . $this->id . '.{jpg|png|gif}', GLOB_BRACE);
		if (count ($files) > 0) {
			$photo = array_shift ($files);
			return '/' . \Image::resize ($photo, $width, $height, 'cover');
		}
		return '/apps/saasy/pix/profile.png';
	}
}

?>