<?php

/**
 * Provides the search interface.
 */

namespace saasy;

// Authorize user
$res = App::authorize ($page, $tpl);
if (! $res) {
	return;
}

$page->title = __ ('Search');

echo App::search ();

?>