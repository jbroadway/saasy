<?php

/**
 * Installs the database schema.
 */

$this->require_admin ();

$page->layout = 'admin';

$cur = $this->installed ('saasy', $appconf['Admin']['version']);

if ($cur === true) {
	$page->title = __ ('Already installed');
	echo '<p><a href="/saasy/admin">' . __ ('Home') . '</a></p>';
	return;
} elseif ($cur !== false) {
	$this->redirect ('/' . $appconf['Admin']['upgrade']);
}

$page->title = __ ('Installing App') . ': SAASy';

$conn = conf ('Database', 'master');
$driver = $conn['driver'];
$error = false;
$sqldata = sql_split (file_get_contents ('apps/saasy/conf/install_' . $driver . '.sql'));
DB::beginTransaction ();
foreach ($sqldata as $sql) {
	if (! DB::execute ($sql)) {
		$error = DB::error ();
		break;
	}
}

if ($error) {
	DB::rollback ();
	echo '<p class="notice">' . __ ('Error') . ': ' . $error . '</p>';
	echo '<p>Install failed.</p>';
	return;
} else {
	DB::commit ();
}

echo '<p><a href="/saasy/admin">' . __ ('Done.') . '</a></p>';

$this->mark_installed ('saasy', $appconf['Admin']['version']);

?>