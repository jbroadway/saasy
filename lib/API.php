<?php

namespace saasy;

use DB, Mailer, Notifier, Restful, User, Validator, Versions, View;

/**
 * Restful API for the app.
 */
class API extends Restful {
	/**
	 * Get a list of members for a company.
	 *
	 * Accessible at GET /saasy/api/members
	 */
	public function get_members () {
		return App::customer ()->members ();
	}
	
	/**
	 * Add a new member to the current company.
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

		$customer = App::customer ();

		$acct = new Account (array (
			'user' => $u->id,
			'customer' => $customer->id,
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
				'subject' => __ ('You have been added to %s', $customer->name),
				'text' => View::render ('saasy/email/new_account', array (
					'email' => $_POST['email'],
					'name' => $_POST['name'],
					'pass' => $pass,
					'customer_name' => $customer->name
				))
			));
		} catch (Exception $e) {
			error_log ('Email failed (saasy/api/member_add): ' . $e->getMessage ());
		}

		return array (
			'id' => $acct->id,
			'user' => $u->id,
			'customer' => $customer->id,
			'type' => $acct->type,
			'enabled' => $acct->enabled,
			'name' => $u->name,
			'email' => $u->email
		);
	}

	/**
	 * Remove a member from a company.
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

		if ($acct->customer != App::customer ()->id) {
			return $this->error (__ ('Cannot remove accounts from other companies'));
		}

		$u = $acct->user ();

		$orig = array (
			'id' => $acct->id,
			'user' => $u->id,
			'customer' => $acct->customer,
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

	/**
	 * Enable a member account.
	 *
	 * Accessible at POST /saasy/api/member_enable
	 *
	 * Parameters:
	 * - account - ID of account
	 */
	public function post_member_enable () {
		if (! isset ($_POST['account'])) {
			return $this->error (__ ('Parameter required: account'));
		}
		
		$acct = new Account ($_POST['account']);
		if ($acct->error) {
			return $this->error (__ ('Account not found'));
		}

		if ($acct->customer != App::customer ()->id) {
			return $this->error (__ ('Cannot update accounts from other companies'));
		}

		return $acct->enable ();
	}

	/**
	 * Disable a member account.
	 *
	 * Accessible at POST /saasy/api/member_disable
	 *
	 * Parameters:
	 * - account - ID of account
	 */
	public function post_member_disable () {
		if (! isset ($_POST['account'])) {
			return $this->error (__ ('Parameter required: account'));
		}
		
		$acct = new Account ($_POST['account']);
		if ($acct->error) {
			return $this->error (__ ('Account not found'));
		}

		if ($acct->customer != App::customer ()->id) {
			return $this->error (__ ('Cannot update accounts from other companies'));
		}

		return $acct->disable ();
	}

	/**
	 * Autocomplete strings for search.
	 *
	 * Accessible at GET /saasy/api/autocomplete
	 */
	public function get_autocomplete () {
		return App::search_autocomplete ();
	}
}

?>