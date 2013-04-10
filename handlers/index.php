<?php

/**
 * Loads the custom handlers for each section, and provides
 * access control between customers.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$section = isset ($this->params[0])
	? $this->params[0]
	: false;

// Redirect to internal handler if specified
if (in_array ($section, array ('account', 'api', 'login', 'search', 'signup', 'user'))) {
	echo $this->run ('saasy/' . $section);
	return;
}

if (! $section) {
	$this->redirect (App::href () . '/' . App::first_section ());
}

Section::set ($section);

$page->title = Section::name ();

echo Section::body ();

?>