<?php
/**
 * DW Directory Service
 *
 * @link              https://github.com/Daworks/dw-directory
 * @since             1.0.0
 * @package           Daworks
 *
 * @wordpress-plugin
 * Plugin Name:       DW Directory Service
 * Plugin URI:        https://github.com/Daworks/dw-directory
 * Description:       DW Directory Service는 워드프레스에 디렉토리 서비스 기능을 사용할 수 있도록 해줍니다. 디렉토리 서비스를 하려고 하는 페이지에 숏코드 [dw-directory] 를 붙여 넣으면 됩니다.
 * Version:           2.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            디자인아레테
 * Author URI:        https://github.com/Daworks
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
 * Current plugin version.
 */
define( 'DAWORKS_VERSION', '2.0.0' );
define( 'DAWORKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DAWORKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DAWORKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-daworks-activator.php
 */
function activate_daworks() {
	require_once DAWORKS_PLUGIN_DIR . 'includes/class-daworks-activator.php';
	Daworks_Activator::activate();
	Daworks_Activator::install_tables();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-daworks-deactivator.php
 */
function deactivate_daworks() {
	require_once DAWORKS_PLUGIN_DIR . 'includes/class-daworks-deactivator.php';
	Daworks_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_daworks' );
register_deactivation_hook( __FILE__, 'deactivate_daworks' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require DAWORKS_PLUGIN_DIR . 'includes/class-daworks.php';

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

	// Load AJAX handler functions
	require DAWORKS_PLUGIN_DIR . 'includes/functions-daworks.php';
}
run_daworks();
