# Testing Incremental Plugin Changes In CakePHP

I've been working on a project that comprises of multiple (private)
plugins that are all under active development. During the development
process I've discovered running `composer update vendor/plugin` to be
not only tedious, but a huge bottleneck to rapid development. It's also
not very convenient for web testing small changes to things like CSS or
JavaScript, since changes must be committed and pushed before running
composer on the main project.

## Local repositories

One nice feature of composer is the ability to specify local
repositories. This allows you to develop locally and let composer pull
from your local drive. While nice, this still doesn't solve the problem
of having to commit every little change in order to see how the plugin
looks and behaves in the main app's context.

## Local plugins

The solution? **Local Cake plugins.** Perhaps this feature is a
remnant of a time before composer, but it certainly gets the job done.
Using Cake's local (or legacy) plugin settings, we can develop plugins
locally without committing and updating each time to check out changes.
This allows us to develop quickly and still keep purposeful commits in
the public repo when we're ready to commit.

In the main app's `config/bootstrap.php`:

```php
Plugin::load('<vendor>/<plugin>', [
	'routes' => true,
	'bootstrap' => true,
	// the path key allows us to tell Cake where the plugin is
	'path' => '/path/to/plugin'
]);
```

Now all we have to do is tell composer to autoload using the local
plugin instead of the actual one in composer's require key. To do so,
change the autoload settings:

```json
"autoload": {
	"psr-4": {
		"App\\": "src",
		"<vendor>\\<plugin>\\": "/path/to/plugin/src"
	}
}
```

After this change you'll need to `composer dumpautoload` to change the
autoloader to load from the local repository.

## Cleanup

While not a perfect solution, these settings have allowed me to test
multiple local plugins quickly without having to commit and update on
each minor change. Unfortunately, it's not without its drawbacks,
specifically, having to clean up once the changes are committed to the
actual repo. It's not difficult, just check out `composer.json` and
`config/bootstrap.php` and dump the autoloader again.
