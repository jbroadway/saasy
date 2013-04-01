<?php

/**
 * The Elefant admin for viewing customers and
 * high-level management features.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = 'SaaS Manager';

$customers = saasy\Customer::query ()->count ();
$accts = saasy\Account::query ()->where ('type != "owner"')->count ();

printf ('<p>%s: %d</p>', __ ('Customers'), $customers);
printf ('<p>%s: %d</p>', __ ('Member Accounts'), $accts);

?>