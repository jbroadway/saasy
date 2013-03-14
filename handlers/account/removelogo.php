<?php

/**
 * Remove the logo from the account.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$org = App::org ();
$acct = App::acct ();

if ($acct->type != 'owner') {
	$this->redirect (App::href () . '/account');
}

$org->remove_logo ();

// TODO: add notification for user

$this->redirect (App::href () . '/account');

?>