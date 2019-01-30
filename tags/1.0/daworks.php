<?php

/**
 *
 * @link              http://daworks.org
 * @since             1.0.0
 * @package           Daworks
 *
 * @wordpress-plugin
 * Plugin Name:       DW Directory Service
 * Plugin URI:        http://daworks.org
 * Description:       DW Directory Service는 워드프레스에 디렉토리 서비스 기능을 사용할 수 있도록 해줍니다. 디렉토리 서비스를 하려고 하는 페이지에 숏코드 [dw-directory] 를 붙여 넣으면 됩니다.
 * Version:           1.0
 * Author:            디자인아레테
 * Author URI:        http://daworks.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       daworks
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-daworks-activator.php
 */
function activate_daworks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-daworks-activator.php';
	Daworks_Activator::activate();
	Daworks_activator::install_plugins();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-daworks-deactivator.php
 */
function deactivate_daworks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-daworks-deactivator.php';
	Daworks_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_daworks' );
register_deactivation_hook( __FILE__, 'deactivate_daworks' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-daworks.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_daworks() {

	$plugin = new Daworks();
	$plugin->run();

	require plugin_dir_path(__FILE__).'includes/functions-daworks.php'; // ajax 처리 함수들

}
run_daworks();