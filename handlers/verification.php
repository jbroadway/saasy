<?php

/**
 * Embeds a verification reminder if they haven't verified their
 * email yet.
 */

if (! User::is_valid ()) {
	return;
}

$u = User::current ();
$verifier = $u->ext ('verifier');

if ($verifier) {
	echo $tpl->render ('saasy/verification');
}

?>