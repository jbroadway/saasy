<?php

/**
 * Remove the logo from the account.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$acct = App::acct ();

$acct->remove_photo ();

// TODO: add notification for user

$this->redirect (App::href () . '/account');

?>