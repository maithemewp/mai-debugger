<?php

/**
 * Plugin Name:     Mai Debugger
 * Plugin URI:      https://bizbudding.com/mai-theme/
 * Description:     An aggressive debugging plugin with Whoops and Symfony var-dumper.
 * Version:         0.1.2
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
if ( ! ( isset( $_GET['maidebugger'] ) && in_array( $_GET['maidebugger'], [ 'off', 'deactivate' ] ) ) ) {
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
}

/**
 * Force deactivate plugin with query parameter.
 *
 * @return void
 */
add_action( 'template_redirect', function() {
	if ( ! ( isset( $_GET['maidebugger'] ) && 'deactivate' === $_GET['maidebugger'] ) ) {
		return;
	}

	// Deactivate plugin.
	deactivate_plugins( 'mai-debugger/mai-debugger.php' );

	// Redirect to same page without any query parameters.
	wp_safe_redirect( home_url( add_query_arg( [] ) ) );
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

	// Set the stable branch.
	$updater->setBranch( 'main' );

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
