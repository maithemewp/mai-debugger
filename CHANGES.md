# Changelog

## 0.4.0 (2026-08-17)

* Added: `MAI_DEBUGGER_KEY`. Define it in `wp-config.php` and the Whoops error page only renders for a request carrying it as `?maidebugger=<key>`. Everyone else gets WordPress' normal error output, so the plugin can sit on a live site without printing file paths and source code to visitors. Leave the constant undefined and nothing changes.
* Changed: Whoops now escalates only what PHP itself treats as fatal. Uncaught exceptions and real fatals still get the full Whoops page. Notices, warnings and deprecations no longer do.
* Changed: Non-fatal errors are handed back to PHP instead of being suppressed, so they keep landing in `debug.log`. Whoops' own `silenceErrorsInPaths()` was not used for this: it stops PHP's internal handler from running, so the error never gets logged at all.
* Fixed: A single deprecation from a vendored library no longer white-screens the whole site.
* Fixed: `?maidebugger=deactivate` now redirects to the same URL with its own query arg removed. It was using `add_query_arg( [] )`, which returns the current URI untouched, so the redirect landed back on `?maidebugger=deactivate`.
* Fixed: Sites no longer die when a plugin loads translations before `init`. A plugin whose outbound request fails will often build its error message with `__()`, which trips WordPress 6.7's `_load_textdomain_just_in_time` notice. Seen with Download Monitor and WP Migrate DB Pro.

## 0.3.0 (2026-06-01)

* Added: `Requires PHP: 8.1` plugin header, matching Mai Engine.
* Changed: Plugin updates are tag-based instead of branch-based. PUC now uses the latest GitHub tag.
* Changed: Updated yahnis-elsts/plugin-update-checker from 5.6 to 5.7.
* Changed: Updated symfony/var-dumper from 6.4.32 to 6.4.36, staying on the 6.4 LTS line to keep PHP 8.1 support.
* Fixed: Whoops is no longer registered under WP-CLI or PHP CLI. Its HTML error handler turned dependency deprecation notices into exceptions and silently aborted `wp` commands. WP-CLI has its own error reporting.

## 0.2.0 (2026-03-30)

* Changed: Raised the minimum PHP version to 8.1.
* Changed: Updated filp/whoops from 2.14.3 to 2.18.4.
* Changed: Updated symfony/var-dumper from 5.4.x to 6.4.x.
* Changed: Updated psr/log from 1.1.4 to 3.0.2.
* Fixed: Updated yahnis-elsts/plugin-update-checker from 5.5 to 5.6, clearing a PHP 8.4 nullable parameter deprecation.
* Removed: symfony/polyfill-php80, no longer needed.

## 0.1.2

* Changed: Version bump.

## 0.1.1

* Changed: Updated the updater.

## 0.1.0

* Added: Initial release.
