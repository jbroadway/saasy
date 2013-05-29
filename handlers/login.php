<?php

if (! $this->internal) {
	$page->title = __ ('Members');
}

if (isset ($_GET['redirect'])) {
	$_POST['redirect'] = $_GET['redirect'];
}

if (! isset ($_POST['redirect'])) {
	$_POST['redirect'] = $_SERVER['REQUEST_URI'];
	if ($_POST['redirect'] == '/user/login') {
		$_POST['redirect'] = '/user';
	}
}

if (! Validator::validate ($_POST['redirect'], 'header')) {
	$_POST['redirect'] = '/user';
}

if (! User::require_login ()) {
	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p>' . __ ('Incorrect email or password, please try again.') . '</p>';
	}
	$_POST['signup_handler'] = false;
	echo $tpl->render ('user/login', $_POST);
} else {
	$customer = saasy\App::customer ();
	if (! $customer) {
		$acct = saasy\Account::query ()
			->where ('user', User::val ('id'))
			->single ();

		if ($acct && ! $acct->error) {
			$customer = new saasy\Customer ($acct->customer);
			if (! $customer->error) {
				$this->redirect ('//' . $customer->domain () . '/');
			}
		}
	}
	$this->redirect ($_POST['redirect']);
}

?>