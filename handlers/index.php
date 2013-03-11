<?php

namespace saasy;

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