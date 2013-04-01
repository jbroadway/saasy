<?php

/**
 * Remove the logo from the account.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$customer = App::customer ();
$acct = App::acct ();

if ($acct->type != 'owner') {
	$this->redirect (App::href () . '/account');
}

$customer->remove_logo ();

// TODO: add notification for user

$this->redirect (App::href () . '/account');

?>