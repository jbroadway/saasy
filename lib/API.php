<?php

namespace saasy;

use DB, Mailer, Restful, User, Validator, Versions, View;

/**
 * Restful API for the app.
 */
class API extends Restful {
	/**
	 * Get a list of members for an organization.
	 *
	 * Accessible at GET /saasy/api/members
	 */
	public function get_members () {
		return App::org ()->members ();
	}
	
	/**
	 * Add a new member to the current organization.
	 *
	 * Accessible at POST /saasy/api/member_add
	 *
	 * Parameters:
	 * - name - Name of user
	 * - email - Email of user
	 */
	public function post_member_add () {
		if (! isset ($_POST['name']) || empty ($_POST['name'])) {
			return $this->error (__ ('Parameter required: name'));
		}

		if (! isset ($_POST['email'])) {
			return $this->error (__ ('Parameter required: email'));
		}

		if (! Validator::validate ($_POST['email'], 'email')) {
			return $this->error (__ ('Invalid email address.'));
		}

		if (! Validator::validate ($_POST['email'], 'unique', '#prefix#user.email')) {
			return $this->error (__ ('Email address already in use.'));
		}

		DB::beginTransaction ();
		$date = gmdate ('Y-m-d H:i:s');
		$pass = User::generate_pass (8);
		$u = new User (array (
			'name' => $_POST['name'],
			'email' => $_POST['email'],
			'password' => User::encrypt_pass ($pass),
			'expires' => $date,
			'type' => 'member',
			'signed_up' => $date,
			'updated' => $date,
			'userdata' => json_encode (array ())
		));
		$u->put ();
		if ($u->error) {
			DB::rollback ();
			return $this->error (__ ('Unable to create user account.'));
		}

		Versions::add ($u);

		$org = App::org ();

		$acct = new Account (array (
			'user' => $u->id,
			'org' => $org->id,
			'type' => 'member',
			'enabled' => 1
		));
		$acct->put ();
		if ($acct->error) {
			DB::rollback ();
			return $this->error (__ ('Unable to create user account.'));
		}
		DB::commit ();

		try {
			Mailer::send (array (
				'to' => array ($_POST['email'], $_POST['name']),
				'subject' => __ ('You have been added to %s', $org->name),
				'text' => View::render ('saasy/email/new_account', array (
					'email' => $_POST['email'],
					'name' => $_POST['name'],
					'pass' => $pass,
					'org_name' => $org->name
				))
			));
		} catch (Exception $e) {
			error_log ('Email failed (saasy/api/member_add): ' . $e->getMessage ());
		}

		return array (
			'id' => $acct->id,
			'user' => $u->id,
			'org' => $org->id,
			'type' => $acct->type,
			'enabled' => $acct->enabled,
			'name' => $u->name,
			'email' => $u->email
		);
	}

	/**
	 * Remove a member from an organization.
	 *
	 * Accessible at POST /saasy/api/member_remove
	 *
	 * Parameters:
	 * - account - ID of account
	 */
	public function post_member_remove () {
		if (! isset ($_POST['account'])) {
			return $this->error (__ ('Parameter required: account'));
		}
		
		$acct = new Account ($_POST['account']);
		if ($acct->error) {
			return $this->error (__ ('Account not found'));
		}

		if ($acct->org != App::org ()->id) {
			return $this->error (__ ('Cannot remove accounts from other organizations'));
		}

		$u = $acct->user ();

		$orig = array (
			'id' => $acct->id,
			'user' => $u->id,
			'org' => $acct->org,
			'type' => $acct->type,
			'enabled' => $acct->enabled,
			'name' => $u->name,
			'email' => $u->email
		);

		DB::beginTransaction ();
		if (! $u->remove ()) {
			DB::rollback ();
			error_log ('User remove failed: ' . $u->error);
			return $this->error (__ ('Unable to delete user account.'));
		}

		if (! $acct->remove ()) {
			DB::rollback ();
			error_log ('Account remove failed: ' . $acct->error);
			return $this->error (__ ('Unable to delete user account.'));
		}
		DB::commit ();

		return $orig;
	}
}

?>