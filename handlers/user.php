<?php

/**
 * Redirects /user and /user/update to /saasy/account.
 */

namespace saasy;

$this->redirect (App::href () . '/account');

?>