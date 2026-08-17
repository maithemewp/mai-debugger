<?php

/**
 * Plugin Name:     Mai Debugger
 * Plugin URI:      https://bizbudding.com/mai-theme/
 * Description:     An aggressive debugging plugin with Whoops and Symfony var-dumper.
 * Version:         0.4.0
 * Requires PHP:    8.1
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Must be at the top of the file.
// use Symfony\Component\VarDumper\VarDumper;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Include vendor libraries.
require_once __DIR__ . '/vendor/autoload.php';

// Register the handler.
// Skip in CLI/WP-CLI: Whoops' PrettyPageHandler renders a browser HTML error page and exits,
// and its error handler turns PHP notices/deprecations into exceptions. Under WP-CLI that
// swallows command output and aborts before the command runs (e.g. a dependency emitting a
// PHP 8.4 deprecation would silently kill `wp` output). WP-CLI has its own error reporting.
$is_cli = ( defined( 'WP_CLI' ) && WP_CLI ) || 'cli' === PHP_SAPI;

// The `maidebugger` query arg, if any. `off` and `deactivate` are reserved and checked first,
// so neither can ever be shadowed by a key someone happens to choose.
$mai_debugger_arg = isset( $_GET['maidebugger'] ) ? (string) $_GET['maidebugger'] : '';
$mai_debugger_bypass = in_array( $mai_debugger_arg, [ 'off', 'deactivate' ], true );

/**
 * On a live site, show the error page to whoever holds the key and nobody else.
 *
 * Whoops prints file paths, source code and server details straight into the response, which
 * is fine on a laptop and not fine on a site with visitors. Define MAI_DEBUGGER_KEY in
 * wp-config.php and the error page only renders for a request carrying it:
 *
 *     define( 'MAI_DEBUGGER_KEY', 'some-long-random-string' );
 *     https://example.com/broken-page/?maidebugger=some-long-random-string
 *
 * Everyone else gets WordPress' normal error output. Leave the constant undefined and nothing
 * changes: the error page shows for every request, which is what you want locally.
 *
 * It has to be a key in the URL rather than a capability check. Plugin files run at
 * wp-settings.php:574 and pluggable.php isn't loaded until :604, so wp_get_current_user() and
 * every auth function simply do not exist yet. Waiting for them would mean arming Whoops after
 * plugins_loaded, which is exactly when the errors worth catching tend to fire.
 *
 * The key travels in the URL, so it lands in access logs and browser history. Treat it as a
 * short-lived "let me look at this" token and change it when you're done, not as a credential.
 */
$mai_debugger_gated = defined( 'MAI_DEBUGGER_KEY' ) && '' !== (string) MAI_DEBUGGER_KEY;
$mai_debugger_allowed = ! $mai_debugger_gated || hash_equals( (string) MAI_DEBUGGER_KEY, $mai_debugger_arg );

if ( ! $is_cli && ! $mai_debugger_bypass && $mai_debugger_allowed ) {
	$whoops = new \Whoops\Run();
	$whoops->pushHandler( new \Whoops\Handler\PrettyPageHandler() );
	$whoops->register();

	/**
	 * Don't let Whoops kill the page over something PHP would have survived.
	 *
	 * PHP sorts problems into two piles. Notices, warnings and deprecations go in the
	 * "write it down and keep going" pile. Fatal errors go in the "stop right here" pile.
	 * Out of the box Whoops treats both piles as fatal, so one deprecation buried in some
	 * plugin's vendor folder blanks the whole site.
	 *
	 * This hands the first pile back to PHP, which logs it to debug.log and carries on.
	 * Whoops only takes over for the second pile, which is the part it's actually good at.
	 *
	 * Returning false is what hands it back. Whoops' own silenceErrorsInPaths() returns
	 * true instead, which tells PHP the error is already dealt with, so it never gets
	 * logged at all. Losing errors silently in a debugging plugin is worse than the crash.
	 *
	 * Uncaught exceptions and genuine fatals still get the full Whoops page. Those come in
	 * through its exception and shutdown handlers, and neither one is replaced here.
	 */
	set_error_handler(
		function ( $level, $message, $file = null, $line = null ) use ( $whoops ) {
			$fatal = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

			if ( $level & $fatal ) {
				return $whoops->handleError( $level, $message, $file, $line );
			}

			// Hand back to PHP so it logs as usual, and keep rendering.
			return false;
		}
	);
}

/**
 * Force deactivate plugin with query parameter.
 *
 * template_redirect only fires from wp-includes/template-loader.php, which only wp-blog-header.php
 * pulls in, so this works on front end URLs and never inside wp-admin. Use `?maidebugger=off` if
 * you just want to get through one broken admin screen.
 *
 * @return void
 */
add_action( 'template_redirect', function() {
	if ( ! ( isset( $_GET['maidebugger'] ) && 'deactivate' === $_GET['maidebugger'] ) ) {
		return;
	}

	// Deactivate plugin.
	deactivate_plugins( 'mai-debugger/mai-debugger.php' );

	// Redirect to the same page with our own query arg dropped, leaving any others alone.
	// add_query_arg( [] ) was doing the opposite: it returns the current URI untouched, so the
	// redirect landed back on ?maidebugger=deactivate.
	wp_safe_redirect( home_url( remove_query_arg( 'maidebugger' ) ) );
	exit();
});

/**
 * Setup the updater.
 *
 * composer require yahnis-elsts/plugin-update-checker
 *
 * @since 0.1.0
 *
 * @uses https://github.com/YahnisElsts/plugin-update-checker/
 *
 * @return void
 */
add_action( 'plugins_loaded', function() {
	// Bail if plugin updater is not loaded.
	if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return;
	}

	// Setup the updater.
	$updater = PucFactory::buildUpdateChecker( 'https://github.com/maithemewp/mai-debugger/', __FILE__, 'mai-debugger' );

	// No setBranch() = tag-based updates (PUC uses the latest GitHub tag/release).

	// Maybe set github api token.
	if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
		$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
	}

	// Add icons for Dashboard > Updates screen.
	if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
		$updater->addResultFilter(
			function ( $info ) use ( $icons ) {
				$info->icons = $icons;
				return $info;
			}
		);
	}
});

/**
 * Add an admin bar notice to alert user that the debugger is running.
 *
 * @since 0.1.0
 *
 * @return void
 */
add_action( 'admin_bar_menu', function() {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	global $wp_admin_bar;

	$admin_notice = [
		'parent' => 'top-secondary',
		'id'     => 'environment-notice',
		'title'  => sprintf( '<span class="adminbar--environment-notice">%s</span>', __( 'Debug On', 'mai-debugger' ) ),
	];

	$wp_admin_bar->add_menu( $admin_notice );

}, 9999 );

/**
 * Render the admin bar CSS.
 *
 * @since 0.1.0
 *
 * @return void
 */
add_action( 'admin_bar_init', function() {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	$css ='
		.adminbar--environment-notice {
			color: var(--color-link, #72aee6) !important;
			text-transform: uppercase !important;
		}

		@media only screen and ( min-width: 800px ) {
			#wp-admin-bar-environment-notice {
				display: block;
			}

			#wp-admin-bar-environment-notice .ab-item {
				background-color: var(--color-alt, white) !important;
			}
		}
	';

	wp_add_inline_style( 'admin-bar', $css );

}, 9999 );

/**
 * Run test.
 *
 * @return void
 */
// add_action( 'wp_head', function() {
// 	$value = [
// 		'test' => 'Okay',
// 	];

// 	dump( $value );
// });
