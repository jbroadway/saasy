<?php

/**
 * Provides the search interface.
 */

namespace saasy;

// Authorize user
if (! App::authorize ($page, $tpl)) return;

$page->title = __ ('Search');

echo App::search ();

?>