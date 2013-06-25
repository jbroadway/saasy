<?php

/**
 * Re-sends the email verification.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$u = \User::current ();
$verifier = $u->ext ('verifier');

if (! $verifier) {
	$page->title = __ ('Email already verified.');
	printf ('<p>%s</p>', __ ('Thank you, your email address has already been verified.'));
	printf ('<p><a href="/">%s</a></p>', __ ('Continue'));
	return;
}

$customer = App::customer ();

try {
	\Mailer::send (array (
		'to' => array ($u->email, $u->name),
		'subject' => __ ('Please confirm your email address'),
		'text' => $tpl->render ('saasy/email/verification', array (
			'verifier' => $verifier,
			'email' => $u->email,
			'name' => $u->name,
			'domain' => $customer->domain ()
		))
	));
} catch (\Exception $e) {
	@error_log ('Email failed (saasy/resend-verification): ' . $e->getMessage ());
	$page->title = __ ('An error occurred');
	printf ('<p>%s</p>', __ ('We were unable to send an email at this time. Please try again later.'));
	printf ('<p><a href="/">%s</a></p>', __ ('Continue'));
	return;
}

$page->title = __ ('Verification email sent');
printf ('<p>%s</p>', __ ('Check your inbox for an email with a link to verify your email address.'));
printf ('<p><a href="/">%s</a></p>', __ ('Continue'));

?>