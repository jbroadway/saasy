<?php

/**
 * Provides the search interface.
 */

namespace saasy;

if (! \User::require_login ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$page->title = __ ('Search');

echo App::search ();

?>