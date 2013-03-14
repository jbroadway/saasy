<?php

/**
 * Custom user sign up form that also creates an initial
 * organziation and owner account.
 */

// Sign up at base domain
$org = saasy\App::org ();
if ($org) {
	$this->redirect (
		$this->is_https ()
			? 'https://www.' . saasy\App::base_domain () . '/user/signup'
			: 'http://www.' . saasy\App::base_domain () . '/user/signup'
	);
}

$form = new Form ('post', $this);
$page->title = __ ('Sign Up');

echo $form->handle (function ($form) use ($page, $tpl) {
	$date = gmdate ('Y-m-d H:i:s');
	$verifier = md5 (uniqid (mt_rand (), 1));
	$u = new User (array (
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'expires' => $date,
		'type' => 'member',
		'signed_up' => $date,
		'updated' => $date,
		'userdata' => json_encode (array ('verifier' => $verifier))
	));
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		// Create organization and account
		$org = new saasy\Organization (array (
			'name' => $_POST['org_name'],
			'subdomain' => $_POST['subdomain'],
			'level' => 1
		));
		$org->put ();

		$acct = new saasy\Account (array (
			'user' => $u->id,
			'org' => $org->id,
			'type' => 'owner',
			'enabled' => 1
		));
		$acct->put ();

		try {
			Mailer::send (array (
				'to' => array ($_POST['email'], $_POST['name']),
				'subject' => __ ('Please confirm your email address'),
				'text' => $tpl->render ('user/email/verification', array (
					'verifier' => $verifier,
					'email' => $_POST['email'],
					'name' => $_POST['name']
				))
			));
		} catch (Exception $e) {
			@error_log ('Email failed (saasy/signup): ' . $e->getMessage ());
			$u->userdata = array ();
			$u->put ();
		}

		$_POST['username'] = $_POST['email'];
		User::require_login ();
		$form->controller->redirect (
			$form->controller->is_https ()
				? 'https://' . $org->domain () . '/'
				: 'http://' . $org->domain () . '/'
		);
	}
	@error_log ('Error creating profile: ' . $u->error);
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('Please try again later.') . '</p>';
	echo '<p><a href="/">' . __ ('Back') . '</a></p>';
});

?>