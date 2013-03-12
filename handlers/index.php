<?php

/**
 * Loads the custom handlers for each section, and provides
 * access control between organizations.
 */

namespace saasy;

// Authorize user
$res = App::authorize ($page, $tpl);
if (! $res) {
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