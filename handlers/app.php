<?php

/**
 * Generates the scaffolding for a SaaS app, including the outline of the
 * app's models, views, and handlers, as well as a custom configuration
 * file saved to `conf/app.saasy.config.php`.
 *
 * Usage:
 *
 *     ./elefant saasy/app myapp "My\ App" Dashboard Messages Tasks
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	echo "Usage: elefant saasy/app <appname> <title> [<section>, <section>]\n";
	die;
}

if (! isset ($_SERVER['argv'][3])) {
	echo "Usage: elefant saasy/app <appname> <title> [<section>, <section>]\n";
	die;
}

if (! preg_match ('/^[a-z0-9_-]+$/', $_SERVER['argv'][2])) {
	echo "Input error: Invalid appname.\n";
	echo "Usage: elefant saasy/app <appname> <title> [<section>, <section>]\n";
	die;
}

if (file_exists ('apps/' . $_SERVER['argv'][2])) {
	echo "Input error: App already exists.\n";
	echo "Usage: elefant saasy/app <appname> <title> [<section>, <section>]\n";
	die;
}

$appname = $_SERVER['argv'][2];
$title = $_SERVER['argv'][3];
$sections = array ();

for ($i = 4; $i < count ($_SERVER['argv']); $i++) {
	$model_name = preg_replace ('/[^a-zA-Z0-9_]+/', '', $_SERVER['argv'][$i]);
	$handler_name = strtolower (preg_replace ('/[^a-zA-Z0-9_-]+/', '', $_SERVER['argv'][$i]));
	$table_name = '#prefix#' . $appname . '_' . strtolower (preg_replace ('/[^a-zA-Z0-9_]+/', '', $_SERVER['argv'][$i]));
	$sections[] = array (
		'title' => $_SERVER['argv'][$i],
		'model' => $model_name,
		'handler' => $handler_name,
		'table' => $table_name,
		'appname' => $appname
	);
}

//info ($appname);
//info ($title);
//info ($sections);

mkdir ('apps/' . $appname, 0755, true);
mkdir ('apps/' . $appname . '/conf', 0755, true);
mkdir ('apps/' . $appname . '/handlers', 0755, true);
mkdir ('apps/' . $appname . '/models', 0755, true);
mkdir ('apps/' . $appname . '/views', 0755, true);

if (file_exists ('conf/app.saasy.config.php')) {
	copy ('conf/app.saasy.config.php', 'conf/app.saasy.config.backup.php');
	echo "Found existing conf/app.saasy.config.php, backed up to conf/app.saasy.config.backup.php\n";
}

$conf = file_get_contents ('apps/saasy/conf/config.php');
$conf = preg_replace ('/app_name = .*/', 'app_name = "' . $title . '"', $conf);
$conf = preg_replace ('/app_alias = .*/', 'app_alias = ' . $appname, $conf);
$section_block = '';
$schema = '';
$sqlite = '';
foreach ($sections as $section) {
	$section_block .= sprintf (
		"%s[%s/%s] = %s\n",
		$section['handler'],
		$appname,
		$section['handler'],
		$section['title']
	);

	$section['full_model'] = $appname . '\\' . $section['model'];

	file_put_contents (
		'apps/' . $appname . '/models/' . $section['model'] . '.php',
		$tpl->render ('saasy/app/model', $section)
	);

	file_put_contents (
		'apps/' . $appname . '/handlers/' . $section['handler'] . '.php',
		$tpl->render ('saasy/app/handler', $section)
	);

	file_put_contents (
		'apps/' . $appname . '/views/' . $section['handler'] . '.html',
		$tpl->render ('saasy/app/view', $section)
	);

	$schema .= $tpl->render ('saasy/app/table', $section);
	$sqlite .= $tpl->render ('saasy/app/sqlite', $section);
}
$conf = str_replace ('[Emails]', $section_block . "\n[Emails]", $conf);
file_put_contents ('conf/app.saasy.config.php', $conf);
file_put_contents ('apps/' . $appname . '/conf/install_mysql.sql', $schema);
file_put_contents ('apps/' . $appname . '/conf/install_sqlite.sql', $sqlite);

$dbinfo = conf ('Database', 'master');
$driver = $dbinfo['driver'];

echo "App created in apps/$appname, config created in conf/app.saasy.config.php\n";
echo "Your database schemas is in apps/$appname/conf/install_$driver.sql\n";
echo "Edit this file to add your custom fields, then run:\n";
echo "    ./elefant import-db apps/$appname/conf/install_$driver.sql\n";

?>