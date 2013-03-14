<?php

/**
 * The Elefant admin for viewing organizations and
 * high-level management features.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = 'SaaS Manager';

$orgs = saasy\Organization::query ()->count ();
$accts = saasy\Account::query ()->where ('type != "owner"')->count ();

printf ('<p>%s: %d</p>', __ ('Organizations'), $orgs);
printf ('<p>%s: %d</p>', __ ('Member Accounts'), $accts);

?>