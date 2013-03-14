<?php

/**
 * Loads the REST API for the member accounts.
 */

// Authorize user
if (! saasy\App::authorize ($page, $tpl)) return;

$this->restful (new saasy\API);

?>