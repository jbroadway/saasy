<?php

/**
 * Provides organization and account management.
 */

namespace saasy;

// Authorize user
$res = App::authorize ($page, $tpl);
if (! $res) {
	return;
}

$page->title = __ ('Account');

$form = new \Form ('post', $this);

$org = App::org ();
$acct = App::acct ();

$form->data = array (
	'name' => \User::val ('name'),
	'email' => \User::val ('email'),
	'photo' => $acct->photo (),
	'org_name' => $org->name,
	'subdomain' => $org->subdomain,
	'org_logo' => $org->logo ()
);

$acct = App::acct ();
if ($acct->type === 'owner') {
	$form->view = 'saasy/account_owner';
	$form->rules = parse_ini_file ('apps/saasy/forms/account_owner.php', true);
	$page->add_script ('/apps/saasy/js/bootstrap-filestyle-0.1.0.min.js');
}

echo $form->handle (function ($form) use ($page, $org, $acct) {
	// update user/acct
	\User::val ('name', $_POST['name']);
	\User::val ('email', $_POST['email']);
	if (! empty ($_POST['new_pass'])) {
		\User::val ('password', \User::encrypt_pass ($_POST['new_pass']));
	}
	\User::save ();

	if (is_uploaded_file ($_FILES['photo']['tmp_name'])) {
		$acct->save_photo ($_FILES['photo']);
	}
	
	if ($acct->type === 'owner') {
		// update org too
		$org->name = $_POST['org_name'];
		if ($org->subdomain !== $_POST['subdomain']) {
			$org->subdomain = $_POST['subdomain'];
			$domain_has_changed = true;
		} else {
			$domain_has_changed = false;
		}

		if (is_uploaded_file ($_FILES['org_logo']['tmp_name'])) {
			$org->save_logo ($_FILES['org_logo']);
		}

		if ($domain_has_changed) {
			$form->controller->redirect (
				$form->controller->is_https ()
					? 'https://' . $org->subdomain . '.' . App::base_domain () . '/'
					: 'http://' . $org->subdomain . '.' . App::base_domain () . '/'
			);
		}

		// TODO: add notification for user

		return false;
	}
});

?>