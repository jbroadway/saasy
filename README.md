# Saasy - SaaS Manager

Saasy is an [Elefant](http://www.elefantcms.com/) app that provides the glue for
building custom software-as-a-service (SaaS) apps. It provides the basic customer
and account management, [Bootstrap](http://twitter.github.com/bootstrap/index.html)
integration, and app structure, so you can focus on creating the custom functionality
of your SaaS app.

Status: **Beta**

## Screenshot

![Saasy Account management screen](https://raw.github.com/jbroadway/saasy/master/css/saasy-account.png)

## Installation

1. Drop the app into your `apps` folder.
2. Copy the file `apps/saasy/conf/config.php` to `conf/app.saasy.config.php` and
   edit the settings there. This is where most of your customization will occur.
3. Copy the file `apps/saasy/conf/user_config.php` to `conf/app.user.config.php`
   to link the user signup, update, and profile screens to Saasy's handlers.
4. Copy the included `sample_bootstrap.php` into the root of your website and
   rename it `bootstrap.php`.
5. Copy the included `saasy.html` into your `layouts` folder.
6. In the global `conf/config.php` set the `default_handler` to `"saasy/index"`,
   and set the `default_layout` to `"saasy"`.
7. Go to Tools > SaaS Manager to install the database schema for customers and accounts.

**Note:** It's also a good idea to change the `session_domain` setting to `top` in the
global `conf/config.php`. This will ensure logins work across subdomains, so a login to
`www.example.com` also works on `mysubdomain.example.com`.

## To do

* More documentation/examples
* Billing/subscription management
* Admin dashboard to manage customers and accounts
* Customizable theme colours

## Customization

Saasy works by hooking your own app into its various settings. You should only need
to edit the Saasy configuration in `conf/app.saasy.config.php`, but not any of the
Saasy source files directly.

Instead, create a secondary app that will contain all of your models, view templates,
and handlers, and point Saasy to them via the above config file. Here's an example
folder structure for a typical Saasy setup:

```
apps/
	myapp/
		conf/
		handlers/
		models/
		views/
	saasy/
		conf/
		handlers/
		lib/
		models/
bootstrap.php (copied from apps/saasy/sample_bootstrap.php)
conf/
	app.saasy.config.php (copied from apps/saasy/conf/config.php)
layouts/
	saasy.html (copied from apps/saasy/saasy.html)
```

### Using the app scaffold generator

Saasy adds a command to the Elefant command line tool that you can use to automatically
generate the basic scaffolding and configuration file for an app. Here is the format
the command expects:

```
./elefant saasy/app <appname> <title> [<section>, <section>]
```

* `<appname>` is the name of the folder for your app
* `<title>` is the title of your SaaS app
* `<section>` is a list of navbar tabs for your app

For example, to create a basic app called "Project Spot" with navbar options for Messages,
Tasks, Files, and a Wiki, you would say:

```
./elefant saasy/app projectspot "Project Spot" Messages Tasks Files Wiki
```

This will create a preconfigured `conf/app.saasy.config.php`, and the outline of your
app including models, schema, handlers, and views.

From here, you'll need to edit your database schema, found in `apps/<appname>/conf/`,
then import it via:

```
./elefant import-db apps/<appname>/conf/install_mysql.sql
```

Make sure to change the appname and the schema file according to the database your site
is using.

### Adding sections to your SaaS app

To add a new section to your SaaS app, create a handler in your app with the following
layout:

```php
<?php

// Prevent direct access to this handler
if (! saasy\App::authorize ($page, $tpl)) return;

// Your handler logic goes here

?>
```

If your handler's name is `myapp/reports`, to include this in your SaaS menu, add the
following line to `apps/saasy/conf/config.php`'s `[Sections]` section:

```
reports[myapp/reports] = Reports
```

This will now appear as "Reports" in your SaaS app menu.

### Adding a custom theme

To add a custom theme, create a handler in your own app named `theme.php` with the
following contents:

```php
<?php

$page->add_style ('/apps/myapp/css/custom.css');

?>
```

Next, create the file `apps/myapp/css/custom.css` with any custom stylings you want.
Note that Saasy uses Bootstrap for its layout, so you can refer to their website for
class and element names for styling.

And finally, edit the config and set the `theme` to point to our new handler like this:

```
theme = myapp/theme
```

This was done as a handler so that you can include any number of initializations for
your app's needs.

### Setting the SaaS app's base URL

Since we're not accessing our custom app directly, and we don't want all of our URLs
to begin with `/saasy/`, we can set the `app_alias` setting to another name of our
choosing, and Saasy will automatically alias that to point to our app. So if we want
to access our SaaS app at the URL `/myapp`, then we would set `app_alias` like this:

```
app_alias = myapp
```

### Getting the current customer ID

To keep the data from one customer separate from the others, you will need to add
a customer field to your models to store the customer ID. The customer ID is an
integer value, which you can get from your custom app via:

```php
<?php

$customer_id = \saasy\App::customer()->id;

// do something with $customer_id

?>
```

You can also get the customer object itself just by calling `\saasy\App::customer()`.

### Enforcing account limits

Saasy looks to your app to define what account limits, if any, are needed. This is done
by pointing the `limits` setting in your `conf/app.saasy.config.php` file to a method
call defined in your app. For example:

```
limits = "myapp\Account::limits"
```

This would correspond with a class in the file `apps/myapp/lib/Account.php` that looks
like this:

```php
<?php

namespace myapp;

class Account {
	/**
	 * Returns a list of account limits.
	 */
	public static function limits () {
		return array (
			1 => array (
				'name' => __ ('Basic'),
				'members' => 0 // no sub-accounts
			),
			2 => array (
				'name' => __ ('Standard'),
				'members' => 5 // up to 5 member accounts
			),
			3 => array (
				'name' => __ ('Pro'),
				'members' => -1 // unlimited members
			)
		);
	}
}

?>
```

Notice that the array keys explicitly start from `1`, which is because `0` means a
disabled account.

The `members` value is used in the Account area to enforce member account limits.
Additional custom limits can be added here too.

From here, you can get the limits for a given customer like this:

```php
<?php

// get the current customer object
$customer = saasy\App::customer ();

// get all limits for the current account level
$limits = saasy\App::limits ($customer->level);

// get a specific limit value, with default to -1 if not set
$member_limit = saasy\App::limit ($customer->level, 'members', -1);

?>
```

As you can see, these methods make it easy to integrate limits into your SaaS app.