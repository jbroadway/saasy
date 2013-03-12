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

echo $tpl->render (
	'saasy/account',
	array (
	)
);

?>