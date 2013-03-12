<?php

/**
 * Loads the custom handlers for each section, and provides
 * access control between organizations.
 */

namespace saasy;

// Send non-org requests to main site signup
$org = App::org ();
if (! $org) {
	$this->redirect (
		$this->is_https ()
			? 'https://www.' . App::base_domain () . '/user/signup'
			: 'http://www.' . App::base_domain () . '/user/signup'
	);
}

if (! \User::require_login ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$section = isset ($this->params[0])
	? $this->params[0]
	: false;

if (! $section) {
	$this->redirect (App::href () . '/' . App::first_section ());
}

Section::set ($section);

$page->title = Section::name ();

echo Section::body ();

?>