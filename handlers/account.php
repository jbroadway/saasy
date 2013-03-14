<?php

/**
 * Provides organization and account management.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

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

$form->data['has_photo'] = ($form->data['photo'] === '/apps/saasy/pix/profile.png') ? false : true;
$form->data['has_logo'] = ($form->data['org_logo']) ? true : false;

// TODO: check limits too
if ($acct->type === 'owner') {
	$limits = App::limits ($org->level);
	$form->data['members'] = $org->members ();
	$form->data['member_limit'] = isset ($limits['members']) ? $limits['members'] : -1;
	$form->data['account_level'] = isset ($limits['name']) ? $limits['name'] : false;
}

$acct = App::acct ();
if ($acct->type === 'owner') {
	$form->view = 'saasy/account_owner';
	$form->rules = parse_ini_file ('apps/saasy/forms/account_owner.php', true);
	$page->add_style ('/apps/saasy/css/account_members.css');
	$page->add_script ('/apps/saasy/js/bootstrap-filestyle-0.1.0.min.js');
	$page->add_script ('/apps/admin/js/handlebars-1.0.rc.1.js');
	$page->add_script ('/apps/saasy/js/account_members.js');
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
		if (! $org->put ()) {
			return false;
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

		$form->controller->redirect (App::href () . '/account');
	}
});

?>