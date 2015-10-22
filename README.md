## Maintainer
* Julian Scheuchenzuber <js@lvl51.de>

## Installation
```
composer require level51/more-admins

```

If you don't like composer you can just download and unpack it to **more-admins/** under the root of your SilverStripe project.

## Setup
1. Install the module properly
2. Extend your **_ss_environment.php** file with multiple admin credentials, e.g.:
```php
global $_DEFAULT_ADMINS;
$_DEFAULT_ADMINS['root@root.de'] = 'root';
$_DEFAULT_ADMINS['test@test.de'] = 'mypw1';
```
Do a *dev/build?flush=all* and you are done!

## Notes
If your are using this module you do not need to specify a default admin the default way via `Security::setDefaultAdmin()` or the constants `SS_DEFAULT_ADMIN_USERNAME` and `SS_DEFAULT_ADMIN_PASSWORD` since the module will use the first entry in the `$_DEFAULT_ADMINS` array if there is no default admin, yet.
