<?php

/**
 * Provides customer and account management.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$page->title = __ ('Account');

$form = new \Form ('post', $this);

$customer = App::customer ();
$acct = App::acct ();
$limits = App::limits ($customer->level);

$form->data = array (
	'name' => \User::val ('name'),
	'email' => \User::val ('email'),
	'photo' => $acct->photo (),
	'customer_name' => $customer->name,
	'subdomain' => $customer->subdomain,
	'customer_logo' => $customer->logo ()
);

$form->data['has_photo'] = ($form->data['photo'] === '/apps/saasy/pix/profile.png') ? false : true;
$form->data['has_logo'] = ($form->data['customer_logo']) ? true : false;

if ($acct->type === 'owner') {
	$form->data['members'] = $customer->members ();
	$form->data['member_limit'] = isset ($limits['members']) ? (int) $limits['members'] : -1;
	$form->data['account_level'] = isset ($limits['name']) ? $limits['name'] : false;

	$form->view = 'saasy/account_owner';
	$form->rules = parse_ini_file ('apps/saasy/forms/account_owner.php', true);
	$page->add_style ('/apps/saasy/css/account_members.css');
	$page->add_script ('/apps/saasy/js/bootstrap-filestyle-0.1.0.min.js');
	$page->add_script ('/apps/admin/js/handlebars-1.0.rc.1.js');
	$page->add_script ('/apps/saasy/js/account_members.js');
} else {
	$page->add_script ('/apps/saasy/js/bootstrap-filestyle-0.1.0.min.js');
}

echo $form->handle (function ($form) use ($page, $customer, $acct) {
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
		// update customer too
		$customer->name = $_POST['customer_name'];
		if ($customer->subdomain !== $_POST['subdomain']) {
			$customer->subdomain = $_POST['subdomain'];
			$domain_has_changed = true;
		} else {
			$domain_has_changed = false;
		}
		if (! $customer->put ()) {
			return false;
		}

		if (is_uploaded_file ($_FILES['customer_logo']['tmp_name'])) {
			$customer->save_logo ($_FILES['customer_logo']);
		}

		if ($domain_has_changed) {
			echo \View::render (
				'saasy/account_redirect',
				array (
					'redirect' => $form->controller->is_https ()
						? 'https://' . $customer->subdomain . '.' . App::base_domain () . '/'
						: 'http://' . $customer->subdomain . '.' . App::base_domain () . '/'
				)
			);
			return;
		}
	}

	\Notifier::add_notice (__ ('Your settings have been updated.'));
	$form->controller->redirect (App::href () . '/account');
});

?>