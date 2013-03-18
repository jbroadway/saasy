# Saasy - SaaS Manager

Saasy is an [Elefant](http://www.elefantcms.com/) app that provides the glue for
building custom software-as-a-service (SaaS) apps. It provides the basic user
account management, [Bootstrap](http://twitter.github.com/bootstrap/index.html)
integration, and app structure, so you can focus on creating the custom functionality
of your SaaS app.

Status: Beta

## Screenshot

![Saasy Account management screen](https://raw.github.com/jbroadway/saasy/master/css/saasy-account.png)

## Installation

1. Drop the app into your `apps` folder.
2. Copy the file `apps/saasy/conf/config.php` to `conf/app.saasy.config.php` and
   edit the settings there. This is where most of your customization will occur.
3. Copy the included `sample_bootstrap.php` into the root of your website and
   rename it `bootstrap.php`.
4. Copy the included `saasy.html` into your `layouts` folder.
5. In the global `conf/config.php` set the `default_handler` to `"saasy/index"`,
   and set the `default_layout` to `"saasy"`.
6. Go to Tools > SaaS Manager to install the database schema for organizations and accounts.

## To do

* Enforce account limits
* Documentation/examples

## Future

* Admin dashboard to manage organizations and accounts
* Customizable theme colours
* Billing/subscription management

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

### Adding sections to your SAAS app

To add a new section to your SAAS app, create a handler in your app with the following
layout:

```php
<?php

// Prevent direct access to this handler
if (! saasy\App::authorize ($page, $tpl)) return;

// Your handler logic goes here

?>
```

If your handler's name is `myapp/reports`, to include this in your SAAS menu, add the
following line to `apps/saasy/conf/config.php`'s `[Sections]` section:

```
reports[myapp/reports] = Reports
```

This will now appear as "Reports" in your SAAS app menu.

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

### Setting the SAAS app's base URL

Since we're not accessing our custom app directly, and we don't want all of our URLs
to begin with `/saasy/`, we can set the `app_alias` setting to another name of our
choosing, and Saasy will automatically alias that to point to our app. So if we want
to access our SAAS app at the URL `/myapp`, then we would set `app_alias` like this:

```
app_alias = myapp
```
