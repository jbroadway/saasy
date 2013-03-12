<?php

/**
 * This is a sample Elefant bootstrap file that includes
 * the SAASy initializations. Copy the lines below into
 * a file named bootstrap.php in the root of your
 * Elefant installation.
 */

// Pre-initialize the cache
$cache = Cache::init (conf ('Cache'));

// Initialize the app
saasy\App::bootstrap ($controller);

?>