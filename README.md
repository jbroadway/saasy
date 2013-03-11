# SAASy

This app provides the glue for building custom software-as-a-service (SAAS) apps.
It provides the basic user registration capabilities, Bootstrap integration, and
structure, so you can focus on creating the actual functionality of your SAAS app.

## Installation

1. Drop the app into your `apps` folder.
2. Edit the settings in your `apps/saasy/conf/config.php` file.
3. Copy the included `sample_bootstrap.php` into the root of your website and
   rename it `bootstrap.php`.
4. Copy the included `saasy.html` into your `layouts` folder.
5. In the global `conf/config.php` set the `default_handler` to `"saasy/index"`,
   and set the `default_layout` to `"saasy"`.
6. Go to Tools > SAASy for any additional configurations.

## Customization

To do.
