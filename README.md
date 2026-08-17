# Mai Debugger

An aggressive debugging plugin with Whoops and Symfony var-dumper.

Requires PHP 8.1.

## What it does

**Fatal errors and uncaught exceptions** get the full Whoops error page: the message, the line it died on, the surrounding source, and the whole call stack.

**Notices, warnings and deprecations** do not. They go to `debug.log` and the page keeps rendering.

That split is deliberate. Whoops out of the box treats every PHP problem as fatal, so a single deprecation buried in another plugin's vendor folder blanks the whole site, and you never reach the bug you were actually chasing. A white screen also only ever shows you the first problem. Use Query Monitor to see the rest, since it lists every notice for the request and names the plugin responsible.

**Dumping data.** Symfony var-dumper is loaded, so `dump( $some_data )` works anywhere. It prints to the page for everyone who can see that page, including on a live site, and the key below does not gate it. Take your dumps out when you're done.

## Query parameters

All three use the same `maidebugger` parameter and work on any URL.

| Parameter | Effect |
| --- | --- |
| `?maidebugger=off` | Skips Whoops for this one request. The page renders WordPress' normal error output instead. |
| `?maidebugger=deactivate` | Deactivates the plugin, then redirects to the same URL with the parameter stripped. Front end only, see below. |
| `?maidebugger=<your key>` | Shows the Whoops page on a site where `MAI_DEBUGGER_KEY` is set. See Live sites. |

```
https://example.com/broken-page/?maidebugger=off
https://example.com/broken-page/?maidebugger=deactivate
https://example.com/broken-page/?maidebugger=some-long-random-string
```

`off` and `deactivate` are reserved and checked before the key, so choosing either as your key can't lock you out of them.

**`deactivate` is front end only.** It runs on `template_redirect`, which fires from `wp-includes/template-loader.php` and never loads in `wp-admin`. If an admin screen is the thing that's broken, use `?maidebugger=off` to get through it, or deactivate from the plugins screen or WP-CLI.

## Live sites

Whoops prints file paths, source code, server details, and the request's own cookies straight into the response. Fine on a laptop. Not fine on a site with visitors.

Define a key in `wp-config.php`:

```php
define( 'MAI_DEBUGGER_KEY', 'some-long-random-string' );
```

The error page now renders only for a request carrying it:

```
https://example.com/broken-page/?maidebugger=some-long-random-string
```

Everyone else gets WordPress' normal error output. Leave the constant undefined and nothing changes, which is what you want locally.

It has to be a key in the URL rather than a permission check. Plugin files run at `wp-settings.php:574` and `pluggable.php` isn't loaded until `:604`, so `wp_get_current_user()` and every other auth function do not exist yet. Waiting for them would mean arming Whoops after `plugins_loaded`, which is exactly when the errors worth catching tend to fire.

The key travels in the URL, so it lands in access logs and browser history. Treat it as a short-lived "let me look at this" token and change it when you're done.

## WP-CLI

Whoops is not registered under WP-CLI or PHP CLI. Its error page renders browser HTML that reads as nothing useful in a terminal, and because it claimed PHP's exception handler anyway, `wp` commands died silently and reported success. WP-CLI has its own error reporting, which is better in a terminal than anything Whoops does there.

`dump()` still works under WP-CLI and prints readable output.

## Admin bar

An uppercase **Debug On** notice sits in the admin bar whenever the plugin is active, so a site running it is obvious at a glance. It shows on any site where the plugin is active, including one gated behind `MAI_DEBUGGER_KEY`.

## Updates

Updates come from the GitHub repo via Plugin Update Checker, using the latest tag rather than a branch. A release only ships once it's tagged.

For a private repo, or to avoid GitHub's unauthenticated rate limit, define a token in `wp-config.php`:

```php
define( 'MAI_GITHUB_API_TOKEN', 'ghp_...' );
```

If `mai_get_updater_icons()` exists, its icons are used on Dashboard > Updates.
