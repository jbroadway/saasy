<?php

/**
 * Loads the REST API for the member accounts.
 */

$res = saasy\App::authorize ($page, $tpl);
if (! $res) {
	return;
}

$this->restful (new saasy\API);

?>