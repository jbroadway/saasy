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
	$page->add_script ('/apps/saasy/js/bootstrap-filestyle-0.1.0.min.js');
}

echo $form->handle (function ($form) use ($page, $org, $acct) {
	info ($_POST);
});

?>