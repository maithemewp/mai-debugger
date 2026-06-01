# Changelog

## 0.3.0 (2026-06-01)

* Fixed: Whoops is no longer registered under WP-CLI/PHP CLI. Its HTML error handler turned dependency deprecation notices into exceptions and silently aborted `wp` commands; WP-CLI has its own error reporting.
* Added `Requires PHP: 8.1` plugin header (matches Mai Engine).
* Switched plugin updates from branch-based to tag-based (PUC now uses the latest GitHub tag).
* Updated yahnis-elsts/plugin-update-checker from 5.6 to 5.7.
* Updated symfony/var-dumper from 6.4.32 to 6.4.36 (stays on the 6.4 LTS line to keep PHP 8.1 support).

## 0.2.0 (2026-03-30)

* Updated minimum PHP version to 8.1.
* Updated yahnis-elsts/plugin-update-checker from 5.5 to 5.6 (fixes PHP 8.4 nullable parameter deprecation).
* Updated filp/whoops from 2.14.3 to 2.18.4.
* Updated symfony/var-dumper from 5.4.x to 6.4.x.
* Updated psr/log from 1.1.4 to 3.0.2.
* Removed symfony/polyfill-php80 (no longer needed).

## 0.1.2

* Version bump.

## 0.1.1

* Updated updater.

## 0.1.0

* Initial release.
