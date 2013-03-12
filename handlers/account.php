<?php

/**
 * Provides organization and account management.
 */

namespace saasy;

if (! \User::require_login ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$page->title = __ ('Account');

echo $tpl->render (
	'saasy/account',
	array (
	)
);

?>